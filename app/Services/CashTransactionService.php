<?php

namespace App\Services;

use App\Models\V1\CashTransactionable;
use App\Services\Transactions\AbstractTransactionService;
use Illuminate\Support\Facades\Log;

class CashTransactionService extends AbstractTransactionService
{
    public function makeIncomingTransaction($relatedModel, $sum, $paymentMethodID, $paymentDate = null)
    {
        if ($sum == 0)
            return false;

        $this->createTransaction($paymentMethodID, self::IN, $sum, $relatedModel, $paymentDate);
        return true;
    }

    public function makeReplaceIncomingTransactions($relatedModel, $newSum, $newPaymentMethodID, $paymentDate = null)
    {
        $this->makeRollbackTransactions($relatedModel, self::IN);
        $this->makeIncomingTransaction($relatedModel, $newSum, $newPaymentMethodID, $paymentDate);
    }

    public function makeOutcomingTransaction($relatedModel, $sum, $paymentMethodID, $paymentDate = null)
    {
        if ($sum == 0)
            return false;

        $this->createTransaction($paymentMethodID, self::OUT, $sum, $relatedModel, $paymentDate);
        return true;
    }

    public function makeReplaceOutcomingTransactions($relatedModel, $newSum, $newPaymentMethodID, $paymentDate = null)
    {
        Log::debug('makeReplaceOutcomingTransactions', [
            'relatedModel' => $relatedModel,
            'newSum' => $newSum,
            'newPaymentMethodID' => $newPaymentMethodID,
            'paymentDate' => $paymentDate,
        ]);
        $this->makeRollbackTransactions($relatedModel, $this::OUT);
        $this->makeOutcomingTransaction($relatedModel, $newSum, $newPaymentMethodID, $paymentDate);
    }

    public function makeRollbackTransactions($relatedModel, $type = null)
    {
        if ($relatedModel === null)
            return false;

        $transactions = CashTransactionable::where('transactionable_type', get_class($relatedModel))
            ->where('transactionable_id', $relatedModel->id);

        if ($type !== null)
            $transactions = $transactions->where('type', $type);

        $transactions = $transactions->get();
        foreach ($transactions as $transaction) {
            $transaction->handleSetUnconfirm();
            $transaction->delete();
        }

        return true;
    }

    private function createTransaction($paymentMethodID, $type, $sum, $relatedModel, $paymentDate = null)
    {
        if ($sum == 0)
            return false;

        $transaction = new CashTransactionable();
        $transaction->transactionable_id = $relatedModel->id;
        $transaction->transactionable_type = get_class($relatedModel);
        $transaction->payment_method_id = $paymentMethodID;
        $transaction->type = $type;
        $transaction->sum = $sum;

        // Установка даты создания транзакции, если она предоставлена
        if ($paymentDate) {
            $transaction->created_at = $paymentDate;
        }

        $transaction->save();

        // Возможно, нужно установить подтверждение транзакции здесь, если требуется
        $transaction->setConfirmed();

        return $transaction;
    }

    public function checkTransactionsExist($relatedModel, $type)
    {
        $transactions = CashTransactionable::where('transactionable_type', get_class($relatedModel))
            ->where('transactionable_id', $relatedModel->id);

        if ($type !== null)
            $transactions = $transactions->where('type', $type);

        return $transactions->count();
    }
}
