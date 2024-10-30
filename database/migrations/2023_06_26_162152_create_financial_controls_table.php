<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_controls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method_id');
            $table->float('sum');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('payment_method_id')
                  ->references('id')
                  ->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_controls');
    }
}

