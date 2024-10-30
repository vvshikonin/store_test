<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('store_position_id')->constrained('store_positions');
            $table->integer('amount');
            $table->integer('crm_id');
            $table->string('product_refund_user_comment')->nullable()->default(null);
            $table->string('defect_user_comment')->nullable()->default(null);
            $table->boolean('defect_money_refund_status')->nullable()->default(null);
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
        Schema::dropIfExists('order_positions');
    }
}
