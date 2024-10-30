<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountingPeriodToExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedTinyInteger('accounting_month')->nullable()->after('is_paid');
            $table->year('accounting_year')->nullable()->after('accounting_month');
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['accounting_month', 'accounting_year']);
        });
    }
}
