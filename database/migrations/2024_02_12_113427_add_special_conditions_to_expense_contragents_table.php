<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpecialConditionsToExpenseContragentsTable extends Migration
{
    public function up()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->text('special_conditions')->nullable()->after('related_expense_types');
        });
    }

    public function down()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->dropColumn('special_conditions');
        });
    }
}
