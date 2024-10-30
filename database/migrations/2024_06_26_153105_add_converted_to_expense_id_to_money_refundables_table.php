<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConvertedToExpenseIdToMoneyRefundablesTable extends Migration
{
    /**
     * Запуск миграций.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('money_refundables', function (Blueprint $table) {
            // Добавляем колонку converted_to_expense_id после converted_to_expense_at
            $table->foreignId('converted_to_expense_id')->nullable()->after('converted_to_expense_at');
            // Связываем колонку converted_to_expense_id с таблицей expenses
            $table->foreign('converted_to_expense_id', 'fk_money_refundables_expense_id')
                  ->references('id')->on('expenses')->onDelete('set null');
        });
    }

    /**
     * Откат миграций.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('money_refundables', function (Blueprint $table) {
            // Удаляем внешний ключ с явным именем
            $table->dropForeign('fk_money_refundables_expense_id');
            // Удаляем колонку converted_to_expense_id
            $table->dropColumn('converted_to_expense_id');
        });
    }
}