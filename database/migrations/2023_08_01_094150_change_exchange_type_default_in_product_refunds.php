<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeExchangeTypeDefaultInProductRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_refunds', function (Blueprint $table) {
            $table->integer('exchange_type')->default(null)->change();
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
            $table->integer('exchange_type')->default(0)->change();
        });
    }

}
