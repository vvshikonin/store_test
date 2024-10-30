<?php

use App\Models\MoneyRefundIncome;
use App\Models\V1\CashTransactionable;
use App\Models\V1\MoneyRefundable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoneyRefundIncomeDataMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $moneyRefunds = MoneyRefundable::with(['incomes'])->get();

            foreach ($moneyRefunds as $refund) {
                if (count($refund->incomes) == 0 && $refund->refund_sum_money > 0) {

                    if(!$refund->payment_method_id){
                        continue;
                    }

                    $income = $refund->incomes()->create([
                        'sum' => $refund->refund_sum_money,
                        'payment_method_id' => $refund->payment_method_id,
                        'date' => $refund->completed_at ?? now()
                    ]);

                    $transaction = CashTransactionable::where('transactionable_type', MoneyRefundable::class)
                        ->where('transactionable_id', $refund->id)
                        ->first();

                    if($transaction){
                        $transaction->transactionable_type = MoneyRefundIncome::class;
                        $transaction->transactionable_id = $income->id;
                        $transaction->save();
                    }
 
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
