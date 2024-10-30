<?php

namespace App\Events;

use App\Models\V1\InvoiceProduct;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class InvoiceProductRefused
{
    use Dispatchable, SerializesModels;

    public InvoiceProduct $invoiceProduct;

    /**
     * Создает новый экземпляр события.
     *
     * @param \App\Models\V1\InvoiceProduct $invoiceProduct
     * @return void
     */
    public function __construct(InvoiceProduct $invoiceProduct)
    {
        $this->invoiceProduct = $invoiceProduct;
    }
}