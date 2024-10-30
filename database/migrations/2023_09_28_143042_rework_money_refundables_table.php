<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReworkMoneyRefundablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('money_refundables', function (Blueprint $table) {
            $table->decimal('debt_sum', 10, 2)->default(0)->after('refundable_id');
            $table->decimal('refund_sum_money', 10, 2)->default(0)->after('debt_sum');
            $table->decimal('refund_sum_products', 10, 2)->default(0)->after('refund_sum_money');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('money_refundables', function (Blueprint $table) {
            $table->dropColumn('debt_sum');
            $table->dropColumn('refund_sum_money');
            $table->dropColumn('refund_sum_products');
        });
    }
}
