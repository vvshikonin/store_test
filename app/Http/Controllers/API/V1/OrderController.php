<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RetailCRM;
use Illuminate\Support\Facades\DB;
use App\Models\V1\Order;
use App\Models\V1\OrderProduct;
use App\Models\V1\OrderStatus;
use App\Models\V1\Transactionable;
use App\Services\TransactionService;
use App\Services\Entities\Defects\DefectToRetailOrderConverter;
use App\Services\Entities\ProductRefunds\ProductRefundToRetailOrderConverter;
use App\Models\V1\Stock;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockNotification;

class OrderController extends Controller
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        return Order::all();
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);
        $order->load('orderProducts');

        return $order;
    }

    public function store(Request $request)
    {
        if ($request->has('use_retailCRM')) {
            $orderData = RetailCRM::getOrder($request->get('external_id'));
        } else {
            $orderData = collect($request->all());
        }

        $newOrder = Order::create($orderData->except('orderProducts')->all());
        $newOrder->orderProducts()->createMany($orderData->get('orderProducts'));

        $newOrder->load('orderProducts');
        return $newOrder;
    }

    public function update(Request $request, $id)
    {
        if ($request->has('use_retailCRM')) {
            $orderData = RetailCRM::getOrder($request->get('external_id'));
            if ($orderData instanceof \Illuminate\Http\Response) {
                return $orderData;
            }
            $originalOrderCollection = RetailCRM::getOrder($request->get('external_id'));
            $order = Order::firstOrCreate(
                ['external_id' => $request->get('external_id')],
                $orderData->except('orderProducts')->all()
            );
        } else {
            $order = Order::findOrFail($id);
            $orderData = collect($request->all());
        }

        $originalOrderState = $order->state;
        $order->fill($orderData->except('orderProducts')->all());

        // Пока костыль. Потом нужно сделать нормальные настройки статусов._________________________________________
        $reservingStatusCodes = ['availability-confirmed', 'confirmed', 'oplachen-doplatit', 'nedostatochno-ostatka'];
        $outcomingStatusCodes = ['send-to-delivery'];
        $cancelingStatusCodes = ['cancel-other', 'zadvoenie', 'no-product', 'otmena-vozvrat', 'already-buyed', 'vozvrat'];
        $refundStatusCodes = ['vozvratt'];
        $defectStatusCodes = ['brak'];
        $reservingStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $reservingStatusCodes)->get();
        $outcomingStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $outcomingStatusCodes)->get();
        $cancelingStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $cancelingStatusCodes)->get();
        $refundStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $refundStatusCodes)->get();
        $defectStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $defectStatusCodes)->get();
        //__________________________________________________________________________________________________________


        // Внутреннее состояние заказа
        if (count($reservingStatusIDs->where('id', $order->order_status_id))) {
            $order->state = 'reserved';
        } else if (count($outcomingStatusIDs->where('id', $order->order_status_id))) {
            $order->state = 'outcomed';
        } else if (count($cancelingStatusIDs->where('id', $order->order_status_id))) {
            $order->state = 'canceled';
        } else if (count($defectStatusIDs->where('id', $order->order_status_id))) {
            $order->state = 'defect';
        } else if (count($refundStatusIDs->where('id', $order->order_status_id))) {
            $order->state = 'refund';
        }
        $order->save();

        $orderProducts = $orderData->get('orderProducts');
        foreach ($orderProducts as &$OrderProductData) {
            $OrderProductData['order_id'] = $order->id;

            $orderProduct = OrderProduct::where('external_id', $OrderProductData['external_id'])->first();
            if (!$orderProduct)
                $orderProduct = new OrderProduct;

            $orderProduct = $orderProduct->fill($OrderProductData);
            $orderProduct->save();

            if ($orderProduct->contractor_id == 4) {
                $contractorID = null;
            } else {
                $contractorID = $orderProduct->contractor_id;
            }

            // Если заказ стал резервным - проверяем свободный остаток товара и, при необходимости, ставим задачу на снятие акции
            if ($order->state == 'reserved' && $originalOrderState != 'reserved') {
                // Получение всех продуктов из order_products связанных с данным заказом
                $products = $order->orderProducts->map(function ($orderProduct) {
                    return $orderProduct->product;
                });

                foreach ($products as $product) {
                    // Получение данных по каждому продукту, аналогично вашему запросу выше
                    $totalStock = Stock::where('product_id', $product->id)->sum('amount');

                    $reservedStockAmount = OrderProduct::join('orders', 'order_products.order_id', '=', 'orders.id')
                        ->where('order_products.product_id', $product->id)
                        ->where('orders.state', 'reserved')
                        ->sum('order_products.amount');

                    // $freeStock = $totalStock - $reservedStockAmount;

                    // Проверка условия для каждого продукта и обновление статуса акции если необходимо
                    // if ($freeStock > (0.5 * $totalStock)) {
                    //     if ($product->is_sale) {
                    //         $product->is_sale = false;
                    //         $product->save();
                    //         Mail::to('taskstoit@yandex.ru')->send(new LowStockNotification($product->id));
                    //     }
                    // }
                }
            }

            // Если состояние изменилось на резервирование или отмену, то отменяем все транзакции по товару из заказа
            if (
                ($order->state == 'reserved' && $originalOrderState != 'reserved') ||
                ($order->state == 'canceled' && $originalOrderState != 'canceled')
            ) {
                $this->transactionService->makeRollbackTransactions($orderProduct, $this->transactionService::OUT);
            }

            // Если состояние изменилось на списание, то создаём транзакции на списания, записываем закуп в CRM.
            if ($order->state == 'outcomed' && $originalOrderState != 'outcomed') {
                try {
                    $this->transactionService->makeOutcomingTransaction(
                        $orderProduct,
                        $orderProduct->amount,
                        $orderProduct->product_id,
                        $contractorID
                    );
                } catch (\Exception $e) {
                    // Отмена транзакции и установка статуса недостаточно остатка
                    $order->state = 'reserved';
                    $statusID = OrderStatus::select('id')->where('symbolic_code', 'nedostatochno-ostatka')->first()->id;
                    $order->order_status_id = $statusID;
                    $order->save();
                    foreach ($order->orderProducts as $orderProduct) {
                        $this->transactionService->makeRollbackTransactions($orderProduct, $this->transactionService::OUT);
                    }
                    RetailCRM::updateOrder($order);
                    throw $e;
                }

                $avg_price = Transactionable::leftJoin('stocks', 'stocks.id', '=', 'transactionables.stock_id')
                    ->where('transactionable_id', $orderProduct->id)
                    ->where('transactionable_type', OrderProduct::class)
                    ->sum(DB::raw('transactionables.amount * stocks.price'));

                $orderProduct->avg_price = $avg_price / $orderProduct->amount;

                $orderProduct->save();
            }
        }

        $existingCrmExternalIDs = collect($orderProducts)->pluck('external_id');
        OrderProduct::whereNotIn('external_id', $existingCrmExternalIDs)->where('order_id', $order->id)->delete();

        if ($order->state == 'outcomed' && $originalOrderState != 'outcomed') {
            RetailCRM::updateOrder($order);
        }

        // Возврат
        if ($order->state == 'refund' && $originalOrderState != 'refund') {
            $productRefund = $order->productRefund()->withTrashed()->restore();
            if (!$productRefund)
                $order->productRefund()->firstOrCreate();
        } elseif ($order->state != 'refund' && $originalOrderState == 'refund') {
            $order->productRefund()->delete();
        }

        // Браки
        if ($order->state == 'defect' && $originalOrderState != 'defect') {
            $defect = $order->defect()->withTrashed()->restore();
            if (!$defect)
                $order->defect()->firstOrCreate();
        } elseif ($order->state != 'defect' && $originalOrderState == 'defect') {
            $order->defect()->delete();
        }

        if ($order->state == 'defect') {
            DefectToRetailOrderConverter::updateDefectFromOrder($originalOrderCollection);
        }
        if ($order->state == 'refund') {
            ProductRefundToRetailOrderConverter::updateProductRefundFromOrder($originalOrderCollection);
        }

        $order->load('orderProducts');
        return $order;
    }

    public function destroy(Request $request, $id)
    {
        if ($request->has('use_retailCRM')) {
            $order = Order::where('external_id', $request->get('external_id'))->first();
        } else {
            $order = Order::find($id);
        }

        OrderProduct::where('order_id', $order->id)->delete();
        $order->delete();
    }
}
