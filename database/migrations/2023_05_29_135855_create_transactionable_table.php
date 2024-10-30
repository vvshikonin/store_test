<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('contractor_id')->constrained('contractors');
            $table->float('price');
            $table->integer('amount')->default(0);
            $table->integer('saled')->default(0);
            $table->string('user_comment')->nullable();
            $table->boolean('is_sale')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

       Schema::create('transactionables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transactionable_id');
            $table->string('transactionable_type');
            $table->unsignedBigInteger('stock_id');
            $table->string('type');
            $table->integer('amount');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transactionable_id', 'transactionable_type']);
            $table->foreign('stock_id')->references('id')->on('stocks');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactionable');
        Schema::dropIfExists('stoks');
    }
}
