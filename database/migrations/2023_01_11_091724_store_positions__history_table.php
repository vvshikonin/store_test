<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StorePositionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_positions_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_position_id')->constrained('store_positions')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->float('old_price')->nullable()->default(null);
            $table->float('new_price')->nullable()->default(null);
            $table->integer('old_expected')->nullable()->default(null);
            $table->integer('new_expected')->nullable()->default(null);
            $table->integer('old_real_stock')->nullable()->default(null);
            $table->integer('new_real_stock')->nullable()->default(null);
            $table->integer('old_reserved')->nullable()->default(null);
            $table->integer('new_reserved')->nullable()->default(null);
            $table->string('old_user_comment')->nullable()->default(null);
            $table->string('new_user_comment')->nullable()->default(null);
            $table->boolean('old_is_sale')->nullable()->default(null);
            $table->boolean('new_is_sale')->nullable()->default(null);
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
