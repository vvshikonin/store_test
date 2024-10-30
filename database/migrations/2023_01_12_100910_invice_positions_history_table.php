<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvicePositionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_positions_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_position_id')->constrained('invoice_positions')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->foreignId('old_store_position_id')->constrained('store_positions')->nullable()->default(null);
            $table->foreignId('new_store_position_id')->constrained('store_positions')->nullable()->default(null);
            $table->integer('old_amount')->nullable()->default(null);
            $table->integer('new_amount')->nullable()->default(null);
            $table->integer('old_credited')->nullable()->default(null);
            $table->integer('new_credited')->nullable()->default(null);
            $table->integer('old_money_refund')->nullable()->default(null);
            $table->integer('new_money_refund')->nullable()->default(null);
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
