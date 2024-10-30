<?php

namespace App\Services\Transactions\Products;

use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class InvoiceProductTransactions extends TransactionService
{
    /**
     * Создаёт поступление товара.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param App\Modeles\V1\InvoiceProduct $invoiceProduct
     * @param int $amount количестов поступления.
     */
    public function makeProductIncomingFlow($invoice, $invoiceProduct, $amount)
    {
        $this->makeIncomingTransaction(
            $invoiceProduct,
            $amount,
            $invoiceProduct->product_id,
            $invoice->contractor_id,
            $invoiceProduct->price
        );
    }

    /**
     * Создаёт списание товара.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param App\Modeles\V1\InvoiceProduct $invoiceProduct
     * @param int $amount количестов списания.
     */
    public function makeProductOutcomingFlow($invoice, $invoiceProduct, $amount)
    {
        $this->makeOutcomingTransaction(
            $invoiceProduct,
            $amount,
            $invoiceProduct->product_id,
            $invoice->contractor_id,
            $invoiceProduct->price
        );
    }

    /**
     * Заменяет поступление товара.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param App\Modeles\V1\InvoiceProduct $invoiceProduct
     * @param int $amount количестов поступления.
     */
    public function replaceProductFlow($invoice, $invoiceProduct)
    {
        $this->makeReplaceStock(
            $invoiceProduct,
            $invoiceProduct->product_id,
            $invoice->contractor_id,
            $invoiceProduct->price,
        );
    }

    /**
     * Отменяет поступление товара.
     *
     * @param App\Modeles\V1\InvoiceProduct $invoiceProduct
     */
    public function rollbackProductFlow($invoiceProduct)
    {
        $this->makeRollbackTransactions($invoiceProduct);
    }
}
