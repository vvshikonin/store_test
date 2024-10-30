<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('legal_entity_id');
            $table->boolean('is_paid')->default(false);
            $table->json('files')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('legal_entity_id')->references('id')->on('legal_entities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
