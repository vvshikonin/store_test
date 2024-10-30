<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorRefundStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_refund_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contractor_refund_product_id');
            $table->unsignedBigInteger('stock_id');
            $table->integer('amount')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contractor_refund_product_id')->references('id')->on('contractor_refund_products');
            $table->foreign('stock_id')->references('id')->on('stocks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_refund_stocks');
    }
}
