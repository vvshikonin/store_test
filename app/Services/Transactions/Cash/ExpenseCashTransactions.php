<?php

namespace App\Services\Transactions\Cash;

use App\Services\CashTransactionService;
use Illuminate\Support\Facades\Log;

class ExpenseCashTransactions extends CashTransactionService
{
    /**
     * Создаёт списание ДС.
     *
     * @param App\Modeles\V1\Expenses\Expense $expense
     * @param float $sum
     */
    public function makeCashFlow($expense, $sum, $paymentDate = null)
    {
        $this->makeIncomingTransaction(
            $expense,
            $sum,
            $expense->payment_method_id,
            $paymentDate
        );
    }

    /**
     * Заменяет списание ДС.
     *
     * @param App\Modeles\V1\Expenses\Expense $expense
     * @param float $sum
     */
    public function replaceCashFlow($expense, $sum, $paymentDate = null, $paymentMethodId = null)
    {
        Log::info("Идёт замена транзакции\n" . json_encode([
            'Полученные данные' => [
                'expense' => $expense,
                'sum' => $sum,
                'paymentDate' => $paymentDate,
                // 'expense->payment_method' => $expense->payment_method,
                // 'expense->legal_entity' => $expense->legal_entity,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->makeReplaceIncomingTransactions(
            $expense,
            $sum,
            $paymentMethodId,
            $paymentDate
        );
    }

    /**
     * Отменяет списание ДС.
     *
     * @param App\Modeles\V1\Expenses\Expense $expense
     * @param float $sum
     */
    public function rollbackCashFlow($expense)
    {
        $this->makeRollbackTransactions($expense, $this::IN);
    }
}
