<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExchangeTypeFieldToProductRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_refunds', function (Blueprint $table) {
            $table->boolean('exchange_type')->nullable()->default(0);
            $table->string('refund_file')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_refunds', function (Blueprint $table) {
            //
        });
    }
}
