<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToMoneyRefundablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('money_refundables', function (Blueprint $table) {
            $table->bigInteger('payment_method_id')->nullable()->default(null);
            $table->bigInteger('legal_entity_id')->nullable()->default(null);
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
            //
        });
    }
}
