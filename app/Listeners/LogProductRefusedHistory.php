<?php

namespace App\Listeners;

use App\Events\InvoiceProductRefused;
use App\Services\History\InvoiceProductHistoryService;

class LogProductRefusedHistory
{
    protected $invoiceProductHistoryService;

    /**
     * Конструктор слушателя.
     *
     * @param \App\Services\InvoiceProductHistoryService $invoiceProductHistoryService
     * @return void
     */
    public function __construct(InvoiceProductHistoryService $invoiceProductHistoryService)
    {
        $this->invoiceProductHistoryService = $invoiceProductHistoryService;
    }

    /**
     * Обрабатывает событие.
     *
     * @param \App\Events\InvoiceUpdated $event
     * @return void
     */
    public function handle(InvoiceProductRefused $event)
    {
        $invoiceProduct = $event->invoiceProduct;

        $this->invoiceProductHistoryService->handleProductRefused($invoiceProduct);
    }
}