<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\ContractorRefund;
use App\Services\Entities\ContractorRefundService;
use Illuminate\Http\Request;
use App\Http\Requests\ContractorRefund\StoreRequest;
use App\Http\Resources\V1\ContractorRefund\ContractorRefundResource;
use App\Models\V1\Stock;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContractorRefundsExport;
use App\Exports\ContractorRefundProductsExport;
use App\Casts\ContractorRefunds\ContractorRefundDeliveryStatuses;

class ContractorRefundController extends Controller
{
    protected $contractorRefundService;

    public function __construct(ContractorRefundService $contractorRefundService)
    {
        $this->contractorRefundService = $contractorRefundService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $refundsQuery = ContractorRefund::select('contractor_refunds.*', 'refund_sum')
            ->with([
                'contractorRefundProducts',
                'contractorRefundProducts.contractorRefundStocks',
                'contractorRefundProducts.invoiceProduct',
                'invoice',
                'invoice.contractor:id,name',
                'creator',
                'updater'
            ])->contractorRefundSum()
            ->joinInvoices()
            ->joinContractors()
            ->applyFilters($request->all());

        $refundsQuery = $refundsQuery->orderBy($request->sort_field, $request->sort_type);

        $refunds = $refundsQuery->paginate($request->per_page);

        return ContractorRefundResource::collection($refunds);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $refund = $this->contractorRefundService->create($request->all());
        try {
            $refund = $this->contractorRefundService->create($request->all());
            return response()->json(['message' => 'Возврат поставщику успешно создан!', 'data' => $refund], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Не удалось создать возврат поставщику!', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\V1\ContractorRefund  $contractorRefund
     * @return \Illuminate\Http\Response
     */
    public function show(ContractorRefund $contractorRefund)
    {
        $contractorRefund = $this->indexRefundModel($contractorRefund);
        // ->load([
        //     'contractorRefundProducts',
        //     'contractorRefundProducts.contractorRefundStocks',
        //     'contractorRefundProducts.invoiceProduct',
        //     'contractorRefundProducts.invoiceProduct.product',
        //     'invoice',
        //     'invoice.invoiceProducts' => function ($query) {
        //         $stocksSub = Stock::select('product_id')
        //             ->groupBy('product_id')
        //             ->havingRaw('SUM(amount) > 0');
        //         $query->whereRaw('received - refunded > 0')
        //             ->whereIn('product_id', $stocksSub);
        //     },
        //     'invoice.invoiceProducts.stocks' => function ($query) {
        //         $query->where('amount', '>', 0);
        //     },
        //     'invoice.invoiceProducts.product',
        //     'invoice.contractor:id,name',
        //     'invoice.invoiceProducts.stocks.contractor:id,name',
        //     'contractorRefundProducts.contractorRefundStocks.stock',
        //     'contractorRefundProducts.contractorRefundStocks.stock.contractor:id,name',
        //     'creator',
        //     'updater'
        // ]);

        return new ContractorRefundResource($contractorRefund);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\V1\ContractorRefund  $contractorRefund
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContractorRefund $contractorRefund)
    {
        try {
            $contractorRefund = $this->contractorRefundService->update($contractorRefund, $request->all());

            if ($request->has('refund_documents'))
                $contractorRefund->saveContractorRefundFile($request->file('refund_documents'));

            $contractorRefund = $this->indexRefundModel($contractorRefund);
            return response()->json(['message' => 'Возврат поставщику успешно обновлён!', 'data' => $contractorRefund], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Не удалось обновить возврат поставщику!', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Подключает все необходимые данные к основной модели возврата поставщику
     *
     * @param App\Models\V1\ContractorRefund $contractorRefund;
     * @return App\Models\V1\ContractorRefund $contractorRefund;
     */

    public function indexRefundModel($contractorRefund)
    {
        $contractorRefund->load([
            'contractorRefundProducts',
            'contractorRefundProducts.contractorRefundStocks',
            'contractorRefundProducts.invoiceProduct',
            'contractorRefundProducts.invoiceProduct.product',
            'invoice',
            'invoice.invoiceProducts' => function ($query) {
                $stocksSub = Stock::select('product_id')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(amount) > 0');
                $query->whereRaw('received - refunded > 0')
                    ->whereIn('product_id', $stocksSub);
            },
            'invoice.invoiceProducts.stocks' => function ($query) {
                $query->where('amount', '>', 0);
            },
            'invoice.invoiceProducts.product',
            'invoice.contractor:id,name',
            'invoice.invoiceProducts.stocks.contractor:id,name',
            'contractorRefundProducts.contractorRefundStocks.stock',
            'contractorRefundProducts.contractorRefundStocks.stock.contractor:id,name',
            'creator',
            'updater'
        ]);

        return $contractorRefund;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\V1\ContractorRefund  $contractorRefund
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContractorRefund $contractorRefund)
    {
        $this->contractorRefundService->delete($contractorRefund);
    }

    public function export(Request $request)
    {
        $refundsQuery = ContractorRefund::select('contractor_refunds.*', 'refund_sum')
            ->with([
                'contractorRefundProducts',
                'contractorRefundProducts.contractorRefundStocks',
                'contractorRefundProducts.invoiceProduct',
                'invoice',
                'invoice.contractor:id,name',
                'creator',
                'updater'
            ])->contractorRefundSum()
            ->joinInvoices()
            ->joinContractors()
            ->withCasts([
                'delivery_status' => ContractorRefundDeliveryStatuses::class,
            ])
            ->applyFilters($request->all());

        $refunds = $refundsQuery->orderBy($request->sort_field, $request->sort_type)->get();

        return Excel::download(new ContractorRefundsExport($refunds), 'contractor_refunds.xlsx');
    }

    public function exportProducts(Request $request)
    {
        $refundProductsQuery = ContractorRefund::with([
            'contractorRefundProducts',
            'contractorRefundProducts.contractorRefundStocks',
            'contractorRefundProducts.invoiceProduct',
            'contractorRefundProducts.invoiceProduct.product',
            'invoice',
            'invoice.invoiceProducts' => function ($query) {
                $stocksSub = Stock::select('product_id')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(amount) > 0');
                $query->whereRaw('received - refunded > 0')
                    ->whereIn('product_id', $stocksSub);
            },
            'invoice.invoiceProducts.stocks' => function ($query) {
                $query->where('amount', '>', 0);
            },
            'invoice.invoiceProducts.product',
            'invoice.contractor:id,name',
            'invoice.invoiceProducts.stocks.contractor:id,name',
            'contractorRefundProducts.contractorRefundStocks.stock',
            'contractorRefundProducts.contractorRefundStocks.stock.contractor:id,name',
        ])
            ->contractorRefundSum()
            ->withCasts([
                'delivery_status' => ContractorRefundDeliveryStatuses::class,
            ])
            ->applyFilters($request->all());

        $refunds = $refundProductsQuery->orderBy($request->sort_field, $request->sort_type)->get();

        return Excel::download(new ContractorRefundProductsExport($refunds), 'contractor_refunds_detailed.xlsx');
    }
}
