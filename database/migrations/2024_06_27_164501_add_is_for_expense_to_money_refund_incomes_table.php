<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsForExpenseToMoneyRefundIncomesTable extends Migration
{
    /**
     * Запуск миграции.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('money_refund_incomes', function (Blueprint $table) {
            // Добавляем новое поле is_for_expense, которое будет обозначать, что income создан для перевода в хр
            $table->boolean('is_for_expense')->default(false)->after('payment_method_id')->comment('Поступление создано для перевода в хозяйственный расход');
        });
    }

    /**
     * Откат миграции.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('money_refund_incomes', function (Blueprint $table) {
            // Удаляем поле is_for_expense при откате миграции
            $table->dropColumn('is_for_expense');
        });
    }
}