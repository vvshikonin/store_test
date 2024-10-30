<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIsProfitablePurchaseFieldToStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Добавляем новое поле в таблицу stocks
        Schema::table('stocks', function (Blueprint $table) {
            $table->boolean('is_profitable_purchase')->default(false)->after('amount');
        });

        // Добавляем новое разрешение в таблицу permissions
        DB::table('permissions')->insert([
            'name' => 'product_is_profitable_purchase_update',
            'label' => 'Смена статуса "Выгодно купили"',
            'permission_group_id' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Удаляем поле из таблицы stocks
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('is_profitable_purchase');
        });

        // Удаляем разрешение из таблицы permissions
        DB::table('permissions')->where('name', 'product_is_profitable_purchase_update')->delete();
    }
}
