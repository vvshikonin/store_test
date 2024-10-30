<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\Invoice;
use App\Http\Resources\V1\Invoice\InvoiceResource;
use App\Http\Resources\V1\Invoice\InvoiceCollection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Invoices\InvoicesExport;
use App\Services\Entities\InvoiceService;
use App\Casts\NumberFormatCast;
use App\Casts\YesNoCast;
use App\Casts\Invoices\InvoiceStatusCast;
use App\Casts\Invoices\DeliveryTypeCast;
use App\Models\V1\ContractorRefund;
use App\Http\Requests\Invoice\UpdateRequest;
use App\Http\Requests\Invoice\StoreRequest;
use Illuminate\Support\Facades\File;

class InvoiceController extends Controller
{
    /**
     * Возвращает список счетов с учётом фильтров, сортировки и пагинации.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $invoicesQuery = Invoice::select('invoices.*', 'invoice_products.*')
            ->joinContractor()
            ->joinLegalEntity()
            ->joinPaymentMethod()
            ->joinInvoiceProducts()
            ->applyFilters($request->all());

        $totalSum = $invoicesQuery->sum('invoice_products.total_sum');
        $receivedSum = $invoicesQuery->sum('invoice_products.received_sum');
        $refusedSum = $invoicesQuery->sum('invoice_products.refused_sum');
        $expectedSum = $invoicesQuery->sum('invoice_products.expected_sum');

        $invoices = $invoicesQuery->orderBy($request->sort_field, $request->sort_type);

        return new InvoiceCollection(
            $invoices->paginate($request->per_page),
            $totalSum,
            $receivedSum,
            $refusedSum,
            $expectedSum
        );
    }

    /**
     * Создаёт новый счёт.
     *
     * @param  \App\Http\Requests\Invoice\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, InvoiceService $invoiceService)
    {
        $this->authorize('create', Invoice::class);

        $invoice = $invoiceService->create($request->all());

        if ($request->has('new_invoice_files')) {
            $invoice_files = is_string($invoice->invoice_files) ?  json_decode($invoice->invoice_files) : $invoice->invoice_files;
            foreach ($request->get('new_invoice_files') as $invoiceFile) {
                $fileName = str_replace(' ', '_', $invoice->id . '_' . now() . '_' . $invoiceFile->getClientOriginalName());
                $invoice_files[] = $invoiceFile->storeAs('invoices', $fileName, 'public');
            }

            $invoice->invoice_files = $invoice_files;
            $invoice->save();
        }

        if ($request->has('new_payment_files')) {
            $payment_files = is_string($invoice->payment_files) ?  json_decode($invoice->payment_files) : $invoice->payment_files;
            foreach ($request->get('new_payment_files') as $invoiceFile) {
                $fileName = str_replace(' ', '_', $invoice->id . '_' . now() . '_' . $invoiceFile->getClientOriginalName());
                $payment_files[] = $invoiceFile->storeAs('invoices', $fileName, 'public');
            }

            $invoice->payment_files = $payment_files;
            $invoice->save();
        }

        $invoice->loadMissing(['invoiceProducts', 'transactions', 'paymentHistory']);
        return new InvoiceResource($invoice, $this->aggregateRefusalHistory($invoice));
    }

    /**
     * Возвращает данные по определённому счёту по переданному `ID` в запросе.
     *
     * @param  \App\Models\V1\Invoice $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', Invoice::class);
        $invoice->load(['invoiceProducts', 'transactions', 'creator', 'updater', 'paymentHistory']);
        return new InvoiceResource($invoice, $this->aggregateRefusalHistory($invoice));
    }

    /**
     * Обновляет определённый счёт по переданному `ID` в запросе.
     *
     * @param  \App\Http\Requests\Invoice\UpdateRequest  $request
     * @param  \App\Models\V1\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Invoice $invoice, InvoiceService $invoiceService)
    {
        $invoice = $invoiceService->update($invoice, $request->all());

        if ($request->has('deleted_invoice_files')) {
            $invoice_files = is_string($invoice->invoice_files) ?  json_decode($invoice->invoice_files) : $invoice->invoice_files;
            $invoice_files = array_values(array_diff($invoice_files, $request->get('deleted_invoice_files')));
            $invoice->invoice_files = $invoice_files;
            $invoice->save();
        }

        if ($request->has('new_invoice_files')) {
            $invoice_files = is_string($invoice->invoice_files) ?  json_decode($invoice->invoice_files) : $invoice->invoice_files;
            foreach ($request->get('new_invoice_files') as $invoiceFile) {
                $fileName = str_replace(' ', '_', $invoice->id . '_' . now() . '_' . $invoiceFile->getClientOriginalName());
                $invoice_files[] = $invoiceFile->storeAs('invoices', $fileName, 'public');
            }

            $invoice->invoice_files = $invoice_files;
            $invoice->save();
        }

        if ($request->has('deleted_payment_files')) {
            $payment_files = is_string($invoice->payment_files) ?  json_decode($invoice->payment_files) : $invoice->payment_files;
            $payment_files = array_values(array_diff($payment_files, $request->get('deleted_payment_files')));
            $invoice->payment_files = $payment_files;
            $invoice->save();
        }

        if ($request->has('new_payment_files')) {
            $payment_files = is_string($invoice->payment_files) ?  json_decode($invoice->payment_files) : $invoice->payment_files;
            foreach ($request->get('new_payment_files') as $invoiceFile) {
                $fileName = str_replace(' ', '_', $invoice->id . '_' . now() . '_' . $invoiceFile->getClientOriginalName());
                $payment_files[] = $invoiceFile->storeAs('invoices', $fileName, 'public');
            }

            $invoice->payment_files = $payment_files;
            $invoice->save();
        }

        $invoice->loadMissing(['invoiceProducts', 'transactions', 'paymentHistory']);
        return new InvoiceResource($invoice, $this->aggregateRefusalHistory($invoice));
    }

    /**
     * Удаляет определённый счёт по переданному `ID` в запросе.
     *
     * @param \App\Models\V1\Invoice &$invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice, InvoiceService $invoiceService)
    {
        $this->authorize('delete', Invoice::class);
        $invoiceService->delete($invoice);
    }

    /**
     * Обновляет счета по переданный в `$request` `ID` счетов.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request, InvoiceService $invoiceService)
    {
        $invoices = Invoice::whereIn('id', $request->ids)->get();
        foreach ($invoices as $invoice)
            $invoice = $invoiceService->update($invoice, $request->all());

        return response(status: 204);
    }

    /**
     * Удаляет счета по переданный в `$request` `ID` счетов.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(Request $request, InvoiceService $invoiceService)
    {
        $this->authorize('delete', Invoice::class);

        $invoices = Invoice::whereIn('id', $request->ids)->get();
        foreach ($invoices as $invoice)
            $invoice = $invoiceService->delete($invoice);

        return response(status: 204);
    }

    /**
     * Возвращает все `ContractorRefund` связанные с конкретным счётом. 
     * @param int $invoiceID
     */
    public function getRefunds($invoiceID)
    {
        return ContractorRefund::with(['creator'])
            ->contractorRefundSum()
            ->where('invoice_id', $invoiceID)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Создаёт Excel выгрузку по счетам
     *
     * @param  \App\Requests\Invoice\IndexRequest  $request
     * @return \Maatwebsite\Excel\Facades\Excel
     */
    public function export(Request $request)
    {
        $invoicesQuery = Invoice::select('invoices.*', 'invoice_products.*')
            ->withCasts([
                'total_sum' => NumberFormatCast::class,
                'status' => InvoiceStatusCast::class,
                'status_set_at' => 'datetime',
                'received_at' => 'datetime',
                'min_delivery_date' => 'date',
                'max_delivery_date' => 'date',
                'delivery_type' => DeliveryTypeCast::class,
                'payment_status' => YesNoCast::class,
                'payment_confirm' => YesNoCast::class
            ])
            ->joinContractor()
            ->joinLegalEntity()
            ->joinPaymentMethod()
            ->joinInvoiceProducts()
            ->applyFilters($request->all());

        $invoices = $invoicesQuery->orderBy($request->sort_field, $request->sort_type);
        return Excel::download(new InvoicesExport($invoices->get()), 'invoices.xlsx');
    }

    /**
     * Агрегирует историю отказов для всех продуктов в счете.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @return array
     */
    private function aggregateRefusalHistory(Invoice $invoice): array
    {
        $history = [];

        foreach ($invoice->invoiceProducts as $product) {
            foreach ($product->refusesHistory as $refusal) {
                $history[] = [
                    'user_name' => $refusal->user->name,
                    'product_id' => $product->product_id,
                    'product_sku' => $product->product->main_sku,
                    'product_name' => $product->product->name,
                    'amount' => $refusal->amount,
                    'refused_at' => $refusal->created_at->toDateTimeString(),
                ];
            }
        }

        return $history;
    }
}
