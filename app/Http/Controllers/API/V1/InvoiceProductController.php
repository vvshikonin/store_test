<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\Invoice;
use App\Models\V1\InvoiceProduct;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Invoices\InvoiceProductsExport;
use App\Exports\Invoices\ForControlExport;
use App\Exports\Invoices\ForReceiveExport;
use App\Casts\NumberFormatCast;
use App\Casts\YesNoCast;
use App\Casts\Invoices\InvoiceStatusCast;
use App\Casts\Invoices\DeliveryTypeCast;
use App\Http\Resources\V1\Invoice\ContractorRefundResource;

class InvoiceProductController extends Controller
{
    /**
     * Создаёт Excel выгрузку по товарам в счетах
     *
     * @param  Illuminate\Http\Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function export(Request $request)
    {
        $invoicesIDsSub = Invoice::select('invoices.id')
            ->applyFilters($request->all());

        $invoicesProducts = InvoiceProduct::select('invoice_products.*', 'transactionables.*')
            ->withCasts([
                'invoice_status' => InvoiceStatusCast::class,
                'invoice_status_set_at' => 'datetime',
                'price' => NumberFormatCast::class,
                'invoice_date' => 'date',
                'received_at' => 'datetime',
                'planned_delivery_date_from' => 'date',
                'planned_delivery_date_to' => 'date',
                'invoice_delivery_type' => DeliveryTypeCast::class,
                'invoice_payment_status' => YesNoCast::class,
                'invoice_payment_confirm' => YesNoCast::class,
                'invoice_payment_date' => 'date',
                'created_at' => 'datetime'
            ])
            ->joinInvoice()
            ->joinProduct()
            ->JoinTransactions()
            ->whereIn('invoice_id', $invoicesIDsSub);

        return Excel::download(new InvoiceProductsExport($invoicesProducts), 'inv.xlsx');
    }

    /**
     * Создаёт Excel выгрузку по товарам в счетах для контроля закрывающих
     *
     * @param  Illuminate\Http\Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function exportForControl(Request $request)
    {
        $invoicesIDsSub = Invoice::select('invoices.id')
            ->applyFilters($request->all());

        $invoicesProducts = InvoiceProduct::select('invoice_products.*')
            ->addInvoiceReceivedSum()
            ->withCasts([
                'price' => NumberFormatCast::class,
                'invoice_received_sum' => NumberFormatCast::class,
                'invoice_delivery_type' => DeliveryTypeCast::class,
            ])
            ->joinInvoice()
            ->joinProduct()
            ->whereIn('invoice_id', $invoicesIDsSub);

        return Excel::download(new ForControlExport($invoicesProducts->get()), 'inv.xlsx');
    }

    /**
     * Создаёт Excel выгрузку по товарам в счетах для оприходования
     *
     * @param  Illuminate\Http\Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function exportForReceive(Request $request)
    {
        $invoicesIDsSub = Invoice::select('invoices.id')
            ->applyFilters($request->all());


        $invoicesProducts = InvoiceProduct::select('invoice_products.*')
            ->addInvoiceSum()
            ->withCasts([
                'price' => NumberFormatCast::class,
                'invoice_sum' => NumberFormatCast::class
            ])
            ->joinInvoice()
            ->joinProduct()
            ->whereIn('invoice_id', $invoicesIDsSub);

        return Excel::download(new ForReceiveExport($invoicesProducts->get()), 'inv.xlsx');
    }

    /**
     * Возвращает список товаров счёта доступных для возврата.
     *
     * @param int $invoiceId
     * @return Http/Resources/V1/Invoice/ContractorRefundResource
     */
    public function refunds($invoiceId)
    {
        $invoiceProducts = InvoiceProduct::with([
            'product.stocks' => function ($query) {
                $query->where('amount', '>', 0)->with('contractor:id,name');
            }
        ])
            ->where('invoice_id', $invoiceId)
            ->where('received', '>', 0)
            ->where('received', '!=', 'refunded');

        return ContractorRefundResource::collection($invoiceProducts->get());
    }
}
