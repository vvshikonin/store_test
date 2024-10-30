<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebtPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('sum', 10, 2);
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('money_refundable_id');
            $table->timestamps();
    
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('money_refundable_id')->references('id')->on('money_refundables')->onDelete('cascade'); // Предполагая, что у вас есть таблица money_refundables
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debt_payments');
    }
}
