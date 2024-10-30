<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoneyRefundHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_refunds_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('money_refund_id')->constrained('money_refunds')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->boolean('old_status')->nullable()->default(null);
            $table->boolean('new_status')->nullable()->default(null);
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
