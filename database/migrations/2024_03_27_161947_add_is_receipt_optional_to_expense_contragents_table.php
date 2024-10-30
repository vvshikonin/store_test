<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsReceiptOptionalToExpenseContragentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->boolean('is_receipt_optional')->default(false)->after('name');
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
            $table->dropColumn('is_receipt_optional');
        });
    }
}
