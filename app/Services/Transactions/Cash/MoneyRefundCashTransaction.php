<?php

namespace App\Services\Transactions\Cash;

use App\Services\CashTransactionService;

class MoneyRefundCashTransaction extends CashTransactionService
{
    /**
     * Отменяет поступление ДС.
     *
     * @param App\Modeles\V1\Invoice $invoice
     * @param float $sum
     */
    public function rollbackCashFlow($moneyRefund)
    {
        $this->makeRollbackTransactions($moneyRefund, $this::OUT);
    }
}
