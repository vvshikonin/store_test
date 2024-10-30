<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderPositionsHistoryTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_positions_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_positions_id')->constrained('order_positions')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->integer('old_store_position_id')->nullable()->default(null);
            $table->integer('new_store_position_id')->nullable()->default(null);
            $table->integer('old_amount')->nullable()->default(null);
            $table->integer('new_amount')->nullable()->default(null);
            $table->string('old_product_refund_user_comment')->nullable()->default(null);
            $table->string('new_product_refund_user_comment')->nullable()->default(null);
            $table->string('old_defect_user_comment')->nullable()->default(null);
            $table->string('new_defect_user_comment')->nullable()->default(null);
            $table->integer('old_defect_money_refund_status')->nullable()->default(null);
            $table->integer('new_defect_money_refund_status')->nullable()->default(null);
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
