<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExpenseSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_summaries', function (Blueprint $table) {
            $table->dropColumn('financial_result');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expense_summaries', function (Blueprint $table) {
            $table->decimal('financial_result', 15, 2)->storedAs('total_income - total_expenses');
        });
    }
}
