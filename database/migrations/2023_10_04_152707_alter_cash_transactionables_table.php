<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCashTransactionablesTable extends Migration
{
    public function up()
    {
        Schema::table('cash_transactionables', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->change();
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
