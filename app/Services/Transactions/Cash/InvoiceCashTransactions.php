<?php

namespace App\Services\Transactions\Cash;

use App\Services\CashTransactionService;

class InvoiceCashTransactions extends CashTransactionService
{
    /**
     * Создаёт списание ДС.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param float $sum
     */
    public function makeCashFlow($invoice, $sum, $paymentDate = null)
    {
        $this->makeIncomingTransaction(
            $invoice,
            $sum,
            $invoice->payment_method_id,
            $paymentDate
        );
    }

    /**
     * Заменяет списание ДС.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param float $sum
     */
    public function replaceCashFlow($invoice, $sum, $paymentDate = null)
    {
        $this->makeReplaceIncomingTransactions(
            $invoice,
            $sum,
            $invoice->payment_method_id,
            $paymentDate
        );
    }

    /**
     * Отменяет списание ДС.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param float $sum
     */
    public function rollbackCashFlow($invoice)
    {
        $this->makeRollbackTransactions($invoice, $this::IN);
    }
}
