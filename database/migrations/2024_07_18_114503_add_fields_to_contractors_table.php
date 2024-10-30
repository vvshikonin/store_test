<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToContractorsTable extends Migration
{
    /**
     * Запуск миграции.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractors', function (Blueprint $table) {
            // Добавляем новое поле для минимальной суммы заказа (число с запятой)
            // Поле может быть пустым (nullable)
            $table->decimal('min_order_amount', 8, 2)->nullable()->comment('Минимальная сумма заказа');

            // Добавляем новое поле для времени забора (текстовое)
            // Поле может быть пустым (nullable)
            $table->string('pickup_time')->nullable()->comment('Время забора');

            // Добавляем новое поле для склада (текстовое)
            // Поле может быть пустым (nullable)
            $table->string('warehouse')->nullable()->comment('Склад');

            // Добавляем новое поле для отсрочки платежа (bool)
            // По умолчанию значение false
            $table->boolean('payment_delay')->default(false)->comment('Отсрочка платежа');

            // Добавляем новое поле для наличия договора доставки (bool)
            // По умолчанию значение false
            $table->boolean('has_delivery_contract')->default(false)->comment('Есть договор доставки');
        });
    }

    /**
     * Откат миграции.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractors', function (Blueprint $table) {
            // Удаляем поле минимальной суммы заказа
            $table->dropColumn('min_order_amount');
            // Удаляем поле времени забора
            $table->dropColumn('pickup_time');
            // Удаляем поле склада
            $table->dropColumn('warehouse');
            // Удаляем поле отсрочки платежа
            $table->dropColumn('payment_delay');
            // Удаляем поле наличия договора доставки
            $table->dropColumn('has_delivery_contract');
        });
    }
}