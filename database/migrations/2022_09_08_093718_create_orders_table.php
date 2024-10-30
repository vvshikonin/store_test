<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->datetime('date');
            $table->integer('crm_id');
            $table->boolean('product_refund_type')->nullable()->default(null);
            $table->boolean('product_refund_status')->nullable()->default(null);
            $table->dateTime('product_refund_created_at')->nullable()->default(null);
            $table->dateTime('product_refund_completed_at')->nullable()->default(null);
            $table->boolean('defect_status')->nullable()->default(null);
            $table->dateTime('defect_created_at')->nullable()->default(null);
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
        Schema::dropIfExists('orders');
    }
}
