<?php

namespace App\Models\V1;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
class CashTransactionable extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function getContractorAttribute()
    {
        return optional($this->transactionable)->contractor;
    }

    public function setConfirmed()
    {
        if ($this->transactionable_type == DebtPayment::class) {
            return;
        }

        $finansialControls = FinancialControl::where('payment_method_id', $this->payment_method_id)
            ->where('is_confirmed', 0)
            ->where('type', $this->type)
            ->where('contractor_id', $this->transactionable->contractor_id)
            ->get();

        $toConfirmSum = $this->sum - $this->confirmed_sum;

        foreach ($finansialControls as $control) {
            $availableSum = $control->sum - $control->confirmed_sum;

            if ($availableSum > $toConfirmSum) {
                $control->confirmed_sum += $toConfirmSum;
                $control->save();

                $this->confirmed_sum += $toConfirmSum;
                $this->is_confirmed = true;
                $this->save();
                $this->confirmRelated();
                break;
            } else if ($availableSum == $toConfirmSum) {
                $control->confirmed_sum += $toConfirmSum;
                $control->is_confirmed = true;
                $control->save();

                $this->confirmed_sum += $toConfirmSum;
                $this->is_confirmed = true;
                $this->save();
                $this->confirmRelated();
                break;
            } elseif ($availableSum < $toConfirmSum) {
                $control->confirmed_sum += $availableSum;
                $control->is_confirmed = true;
                $control->save();

                $this->confirmed_sum += $availableSum;
                $this->save();

                $toConfirmSum -= $availableSum;
            }
        }
    }

    public function handleSetUnconfirm()
    {
        DB::transaction(function () {

            if ($this->transactionable_type == DebtPayment::class) {
                return;
            }

        $finansialControls = FinancialControl::where('payment_method_id', $this->payment_method_id)
            ->where('is_confirmed', 0)
            ->where('type', $this->type)
            ->where('contractor_id', $this->transactionable->contractor_id)
            ->get();


            /**
             * @var CashTransactionable $transaction
             */
            foreach ($finansialControls as $control) {

                if ($control->confirmed_sum > $this->confirmed_sum) {
                    $control->confirmed_sum -= $this->confirmed_sum;
                    $control->is_confirmed = false;
                    $control->save();

                    $this->confirmed_sum = 0;
                    $this->is_confirmed = false;
                    $this->confirmRelated();
                    $this->save();
                    break;
                } else if ($control->confirmed_sum == $this->confirmed_sum) {
                    $control->confirmed_sum = 0;
                    $control->is_confirmed = false;
                    $control->save();

                    $this->confirmed_sum = 0;
                    $this->is_confirmed = false;
                    $this->confirmRelated();
                    $this->save();
                    break;
                } elseif ($control->confirmed_sum < $this->confirmed_sum) {
                    $this->confirmed_sum -= $control->confirmed_sum;
                    $this->is_confirmed = false;
                    $this->save();
                    $this->confirmRelated();

                    $control->confirmed_sum = 0;
                    $control->is_confirmed = false;
                    $control->save();
                }
            }
        });
    }


    public function confirmRelated()
    {
        if ($this->transactionable_type == DebtPayment::class) {
            return;
        }

        $relatedTransactions = CashTransactionable::where('transactionable_type', $this->transactionable_type)
            ->where('transactionable_id', $this->transactionable_id)
            ->get();

        $isConfirmed = true;
        foreach ($relatedTransactions as $transaction) {
            if ($transaction->is_confirmed == false) {
                $isConfirmed = false;
                break;
            }
        }

        if ($this->transactionable::class == Invoice::class) {
            $this->transactionable->payment_confirm = $isConfirmed;
            $this->transactionable->save();
        } elseif ($this->transactionable::class == MoneyRefundable::class) {
            $this->transactionable->approved = $isConfirmed;
            $this->transactionable->save();
        }
    }
}
