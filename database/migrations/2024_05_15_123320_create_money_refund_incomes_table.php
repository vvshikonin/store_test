<?php

use App\Models\V1\MoneyRefundable;
use App\Models\V1\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyRefundIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_refund_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MoneyRefundable::class, 'money_refundable_id');
            $table->foreignIdFor(PaymentMethod::class, 'payment_method_id');
            $table->decimal('sum', 10, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('money_refund_incomes');
    }
}
