<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrdersHistoryTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->integer('old_product_refund_type')->nullable()->default(null);
            $table->integer('new_product_refund_type')->nullable()->default(null);
            $table->integer('old_product_refund_status')->nullable()->default(null);
            $table->integer('new_product_refund_status')->nullable()->default(null);
            $table->integer('old_defect_status')->nullable()->default(null);
            $table->integer('new_defect_status')->nullable()->default(null);
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
        //
    }
}
