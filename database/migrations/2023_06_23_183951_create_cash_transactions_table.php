<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_transactionables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transactionable_id');
            $table->string('transactionable_type');
            $table->unsignedBigInteger('payment_method_id');
            $table->string('type');
            $table->integer('sum');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transactionable_id']);
            $table->index(['transactionable_type']);
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_transactions');
    }
}
