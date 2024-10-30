<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaintainedBalanceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintained_balances_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintained_balances_id')->constrained('maintained_balances')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->integer('old_amount')->nullable()->default(null);
            $table->integer('new_amount')->nullable()->default(null);
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
