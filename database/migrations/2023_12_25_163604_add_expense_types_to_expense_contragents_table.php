<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpenseTypesToExpenseContragentsTable extends Migration
{
    public function up()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->json('related_expense_types')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->dropColumn('related_expense_types');
        });
    }
}
