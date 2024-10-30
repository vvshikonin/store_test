<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceComparisonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_monitorings', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable()->default(null);
            $table->string('xpath')->nullable()->default(null);
            $table->unsignedBigInteger('product_id');
            $table->timestamps();
    
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_monitorings');
    }
}
