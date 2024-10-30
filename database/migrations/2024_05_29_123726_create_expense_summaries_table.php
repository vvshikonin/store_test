<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_summaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('accounting_month')->unsigned();
            $table->year('accounting_year');
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0);
            $table->decimal('financial_result', 15, 2)->storedAs('total_income - total_expenses');
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
        Schema::dropIfExists('expense_summaries');
    }
}
