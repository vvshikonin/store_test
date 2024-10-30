<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\V1\PaymentMethod;
use App\Traits\UserStamps;
use App\Filters\FinancialControlFilter;
use DB;

class FinancialControl extends Model
{
    use HasFactory;
    use UserStamps;
    use FinancialControlFilter;

    protected $fillable = [
        'payment_method_id',
        'sum',
        'payment_date',
        'contractor_id',
        'employee_id',
        'type',
        'reason',
        'created_by'
    ];

    public function handleSetConfirm()
    {
        DB::transaction(function () {
            $cashTransactions = CashTransactionable::with(['transactionable'])
                ->where('type', '!=', DebtPayment::class)
                ->where('is_confirmed', 0)
                ->where('payment_method_id', $this->payment_method_id)
                ->where('type', $this->type)
                ->get();

            $toConfirmSum = $this->sum - $this->confirmed_sum;

            /**
             * @var CashTransactionable $transaction
             */
            foreach ($cashTransactions as $transaction) {
                if ($transaction->transactionable->contractor_id != $this->contractor_id) {
                    continue;
                }

                $availableSum = $transaction->sum - $transaction->confirmed_sum;

                if ($availableSum > $toConfirmSum) {
                    $transaction->confirmed_sum += $toConfirmSum;
                    $transaction->save();

                    $this->confirmed_sum += $toConfirmSum;
                    $this->is_confirmed = true;
                    $this->save();
                    break;
                } else if ($availableSum == $toConfirmSum) {
                    $transaction->confirmed_sum += $toConfirmSum;
                    $transaction->is_confirmed = true;
                    $transaction->save();
                    $transaction->confirmRelated();

                    $this->confirmed_sum += $toConfirmSum;
                    $this->is_confirmed = true;
                    $this->save();
                    break;
                } elseif ($availableSum < $toConfirmSum) {
                    $transaction->confirmed_sum += $availableSum;
                    $transaction->is_confirmed = true;
                    $transaction->save();
                    $transaction->confirmRelated();

                    $this->confirmed_sum += $availableSum;
                    $this->save();

                    $toConfirmSum -= $availableSum;
                }
            }
        });
    }

    public function handleSetUnconfirm()
    {
        DB::transaction(function () {
            $cashTransactions = CashTransactionable::with(['transactionable'])
                ->where('type', '!=', DebtPayment::class)
                ->where('confirmed_sum', '>', 0)
                ->where('payment_method_id', $this->payment_method_id)
                ->where('type', $this->type)
                ->get();

            /**
             * @var CashTransactionable $transaction
             */
            foreach ($cashTransactions as $transaction) {
                if ($transaction->transactionable->contractor_id != $this->contractor_id) {
                    continue;
                }

                if ($transaction->confirmed_sum > $this->confirmed_sum) {
                    $transaction->confirmed_sum -= $this->confirmed_sum;
                    $transaction->is_confirmed = false;
                    $transaction->save();
                    $transaction->confirmRelated();

                    $this->confirmed_sum = 0;
                    $this->is_confirmed = false;
                    $this->save();
                    break;
                } elseif ($transaction->confirmed_sum == $this->confirmed_sum) {
                    $transaction->confirmed_sum = 0;
                    $transaction->is_confirmed = false;
                    $transaction->save();
                    $transaction->confirmRelated();

                    $this->confirmed_sum = 0;
                    $this->is_confirmed = false;
                    $this->save();
                    break;
                } elseif ($transaction->confirmed_sum < $this->confirmed_sum) {
                    $this->confirmed_sum -= $transaction->confirmed_sum;
                    $this->is_confirmed = false;
                    $this->save();

                    $transaction->confirmed_sum = 0;
                    $transaction->is_confirmed = false;
                    $transaction->save();
                    $transaction->confirmRelated();
                }
            }
        });
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getlegalEntityAttribute()
    {
        return $this->paymentMethod->legalEntity;
    }
}
