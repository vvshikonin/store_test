<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->tinyInteger('type')->nullable()->default(null);
            $table->tinyInteger('status')->nullable()->defautl(null);
            $table->string('comment')->nullable()->default(null);
            $table->string('product_location')->nullable()->default(null);
            $table->dateTime('delivery_date')->nullable()->default(null);
            $table->string('delivery_address')->nullable()->default(null);
            $table->dateTime('completed_at')->nullable()->default(null);
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_refunds');
    }
}
