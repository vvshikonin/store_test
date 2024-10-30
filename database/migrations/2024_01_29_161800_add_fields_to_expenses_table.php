<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_contragents', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('name');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->enum('regular_payment', ['none', 'weekly', 'monthly', 'yearly'])->default('none')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('regular_payment');
        });
    }
}
