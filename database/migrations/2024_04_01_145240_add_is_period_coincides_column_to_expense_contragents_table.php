<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPeriodCoincidesColumnToExpenseContragentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->boolean('is_period_coincides')->default(false)->after('is_receipt_optional');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->dropColumn('is_period_coincides');
        });
    }
}
