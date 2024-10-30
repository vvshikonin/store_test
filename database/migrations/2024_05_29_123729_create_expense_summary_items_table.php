<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseSummaryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_summary_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('expense_summary_id');
            $table->unsignedBigInteger('expense_id');
            $table->timestamps();

            $table->foreign('expense_summary_id')->references('id')->on('expense_summaries')->onDelete('cascade');
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expense_summary_items', function (Blueprint $table) {
            $table->dropForeign(['expense_summary_id']);
            $table->dropForeign(['expense_id']);
        });

        Schema::dropIfExists('expense_summary_items');
    }
}
