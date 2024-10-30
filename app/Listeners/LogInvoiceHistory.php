<?php

namespace App\Listeners;

use App\Events\InvoicePaymentStatusSet;
use App\Services\History\InvoicePaymentHistoryService;

class LogInvoiceHistory
{
    protected $invoicePaymentHistoryService;

    /**
     * Конструктор слушателя.
     *
     * @param \App\Services\InvoicePaymentHistoryService $invoicePaymentHistoryService
     * @return void
     */
    public function __construct(InvoicePaymentHistoryService $invoicePaymentHistoryService)
    {
        $this->invoicePaymentHistoryService = $invoicePaymentHistoryService;
    }

    /**
     * Обрабатывает событие.
     *
     * @param \App\Events\InvoiceUpdated $event
     * @return void
     */
    public function handle(InvoicePaymentStatusSet $event)
    {
        $invoice = $event->invoice;

        $this->invoicePaymentHistoryService->handleParametersSet($invoice);
    }
}