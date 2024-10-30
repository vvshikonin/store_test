<?php

namespace App\Http\Controllers\API\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductRefundResource;
use App\Models\V1\ProductRefund;
use App\Models\V1\Product;
use App\Exports\ProductRefundsExport;
use App\Exports\ProductRefundsPositionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Services\Entities\ProductRefunds\ProductRefundService;

use App\Casts\ProductRefunds\ProductRefundStatusCast;
use App\Casts\ProductRefunds\ProductRefundTypeCast;
use App\Casts\ProductRefunds\ProductRefundExchangeTypeCast;


class ProductRefundController extends Controller
{

    /**
     * Отображает список всех ресурсов.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $productRefundsQuery = ProductRefund::select('product_refunds.*')
            ->joinOrder()
            ->applyFilters($request->all());

        $productRefunds = $productRefundsQuery->orderBy($request->get('sort_field'), $request->get('sort_type'));

        $productRefunds->with(['orderProducts']);

        return ProductRefundResource::collection($productRefunds->paginate($request->get('per_page')));
    }

    /**
     * Отображает конкретный экземпляр ProductRefund.
     *
     * @param App\Models\V1\ProductRefund $productRefund
     * @return \Illuminate\Http\Response
     */

    public function show(ProductRefund $productRefund)
    {
        $this->authorize('view', $productRefund);

        $productRefund->load(['orderProducts', 'order', 'creator', 'updater']);

        return new ProductRefundResource($productRefund);
    }

    /**
     * Обрабатывает обновление ProductRefund.
     *
     * @param Illuminate\Http\Request $request
     * @param App\Models\V1\ProductRefund $productRefund
     * @param App\Services\Entities\ProductRefunds\ProductRefundService $productRefundService
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, ProductRefund $productRefund, ProductRefundService $productRefundService)
    {
        $this->authorize('update', $productRefund);

        if ($request->has('refundFile')) {
            $productRefund->storeFile($request->file('refundFile'));
        }

        $updatedProductRefund = $productRefundService->update($productRefund, $request->all());

        return new ProductRefundResource($updatedProductRefund->load(['orderProducts', 'order']));
    }

    /**
     * Создаёт Excel выгрузку по возвратам товаров
     *
     * @param  Illuminate\Http\Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function export(Request $request)
    {
        $sortField = $request->get('sort_field');
        $sortType = $request->get('sort_type');

        $productRefunds = ProductRefund::select('product_refunds.*')
            ->withCasts([
                'status' => ProductRefundStatusCast::class,
            ])
            ->joinOrder()
            ->applyFilters($request->all());

        $productRefunds = $productRefunds->orderBy($sortField, $sortType)->get();

        return Excel::download(new ProductRefundsExport($productRefunds), 'product_refunds.xlsx');
    }

    /**
     * Создаёт Excel выгрузку по товарам в возвратах
     *
     * @param  Illuminate\Http\Request $request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function exportProducts(Request $request)
    {
        $filters = $request->all();

        $productRefundIds = ProductRefund::applyFilters($filters)->select('product_refunds.id');

        $products = Product::leftJoin('order_products', 'order_products.product_id', '=', 'products.id')
            ->leftJoin('product_refunds', 'product_refunds.order_id', '=', 'order_products.order_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
            ->leftJoin('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
            ->leftJoin('contractors', 'contractors.id', '=', 'order_products.contractor_id')
            ->select(
                'product_refunds.*',
                'orders.number as order_number',
                'order_statuses.name as order_status_name',
                'products.main_sku',
                'products.name',
                'order_products.amount',
                'order_products.avg_price',
                'contractors.name as contractor_name'
            )
            ->withCasts([
                'status' => ProductRefundStatusCast::class,
            ])
            ->whereIn('product_refunds.id', $productRefundIds)
            ->where('order_products.deleted_at', NULL)
            ->get();

        return Excel::download(new ProductRefundsPositionsExport($products), 'product_refunds.xlsx');
    }
}
