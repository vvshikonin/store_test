<?php

namespace App\Events;

use App\Models\V1\Invoice;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class InvoicePaymentStatusSet
{
    use Dispatchable, SerializesModels;

    public Invoice $invoice;

    /**
     * Создает новый экземпляр события.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}