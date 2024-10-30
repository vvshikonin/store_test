<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToExpenseItemsTable extends Migration
{
    public function up()
    {
        Schema::table('expense_items', function (Blueprint $table) {
            $table->string('name')->nullable()->after('custom_name');
        });
    }

    public function down()
    {
        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropColumn('name'); // Удаление поля при откате миграции
        });
    }
}
