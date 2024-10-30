<?php

namespace App\Services\Entities;

use App\Services\CashTransactionService;
use App\Models\V1\DebtPayment;
use App\Models\V1\MoneyRefundable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DB;
use App\Models\V1\PaymentMethod;
use Illuminate\Support\Facades\Log;
class DebtPaymentService
{
    protected $CTS;

    public function __construct()
    {
        $this->CTS = new CashTransactionService();
    }

    public function handlePayment($invoice)
    {
        return DB::transaction(function () use ($invoice) {
            $debtPaymentsSum = $invoice->debtPayments()->sum('sum');

            if ($debtPaymentsSum < $invoice->debt_payment) {
                $this->processNewPayments($invoice, $debtPaymentsSum);
            } else {
                $this->refundOverpaidAmounts($invoice, $debtPaymentsSum);
            }
        });
    }

    private function processNewPayments($invoice, $debtPaymentsSum)
    {
        $newPaymentSum = $invoice->debt_payment - $debtPaymentsSum;
        $availableSum = $this->getAvailableRefundSum($invoice);

        if ($availableSum < $newPaymentSum) {
            throw new HttpException(422, 'Сумма оплаты долгом превышает долг поставщика!');
        }

        $moneyRefunds = $this->getPaymentableRefunds($invoice);

        foreach ($moneyRefunds as $moneyRefund) {
            $paymentSum = min($moneyRefund->remainingDebt, $newPaymentSum);

            Log::debug('moneyRefund->remainingDebt: ' . $moneyRefund->remainingDebt);
            Log::debug('newPaymentSum: ' . $newPaymentSum);
            Log::debug('paymentSum: ' . $paymentSum);

            $newPaymentSum -= $paymentSum;

            $this->createDebtPayment($invoice, $moneyRefund, $paymentSum);

            if ($newPaymentSum == 0) {
                break;
            }
        }
    }

    private function getAvailableRefundSum($invoice)
    {
        return MoneyRefundable::where('contractor_id', $invoice->contractor_id)
            ->whereRaw('(refund_sum_money + refund_sum_products) < debt_sum')
            ->sum(DB::raw('debt_sum - (refund_sum_money + refund_sum_products)'));
    }

    private function getPaymentableRefunds($invoice)
    {
        return MoneyRefundable::where('contractor_id', $invoice->contractor_id)
            ->whereRaw('(refund_sum_money + refund_sum_products) < debt_sum')
            ->get();
    }

    private function createDebtPayment($invoice, $moneyRefund, $paymentSum)
    {
        $newDebtPayment = DebtPayment::create([
            'sum' => $paymentSum,
            'invoice_id' => $invoice->id,
            'money_refundable_id' => $moneyRefund->id,
        ]);

        $moneyRefund->refund_sum_products += $paymentSum;
        if(($moneyRefund->refund_sum_products + $moneyRefund->refund_sum_money) == $moneyRefund->debt_sum){
            $moneyRefund->status = 1;
        }
        $moneyRefund->save();

        $paymentMethod = PaymentMethod::where('legal_entity_id', $invoice->legal_entity_id)
            ->withoutGlobalScope('excludeType3')
            ->where('type', 3)
            ->first();
        $this->CTS->makeOutcomingTransaction($newDebtPayment, $paymentSum, $paymentMethod->id);
    }

    private function refundOverpaidAmounts($invoice, $debtPaymentsSum)
    {
        $overpaidAmount = $debtPaymentsSum - $invoice->debt_payment;

        foreach ($invoice->debtPayments as $debtPayment) {
            if ($overpaidAmount <= 0) {
                break;
            }

            $refundAmount = min($debtPayment->sum, $overpaidAmount);

            $this->processRefund($debtPayment, $refundAmount);

            $overpaidAmount -= $refundAmount;
        }
    }

    private function processRefund($debtPayment, $refundAmount)
    {
        $moneyRefund = $debtPayment->moneyRefundable;

        $this->CTS->makeRollbackTransactions($debtPayment);

        $moneyRefund->refund_sum_products -= $refundAmount;
        $moneyRefund->save();

        if ($debtPayment->sum == $refundAmount) {
            $debtPayment->delete();
        } else {
            $debtPayment->sum -= $refundAmount;
            $debtPayment->save();
        }
    }
}
