<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorRefundProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_refund_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contractor_refund_id');
            $table->unsignedBigInteger('invoice_product_id');
            $table->integer('amount')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contractor_refund_id')->references('id')->on('contractor_refunds');
            $table->foreign('invoice_product_id')->references('id')->on('invoice_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_refund_products');
    }
}
