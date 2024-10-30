<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('contractors');
            $table->string('sku');
            $table->float('price');
            $table->string('name')->nullable();
            $table->integer('expected')->default('0');
            $table->integer('real_stock')->default('0');
            $table->integer('reserved')->default('0');
            $table->integer('saled_amount')->default('0');
            $table->string('user_comment')->nullable()->default(null);
            $table->boolean('is_sale')->default(0);
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
        Schema::dropIfExists('store_positions');
    }
}
