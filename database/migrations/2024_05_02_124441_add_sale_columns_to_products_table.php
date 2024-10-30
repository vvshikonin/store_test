<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Добавление enum поля sale_type с вариантами auto, multiplier, percent, fixed
            $table->enum('sale_type', ['auto', 'multiplier', 'percent', 'fixed'])
                  ->default('auto')->after('is_sale');

            // Добавление поля sale_multiplier для умножающего множителя распродажи
            $table->decimal('sale_multiplier', 8, 2)->nullable()->after('sale_type');

            // Добавление поля sale_percent для процента распродажи
            $table->decimal('sale_percent', 5, 2)->nullable()->after('sale_multiplier');

            // Добавление поля sale_fixed_price для фиксированной цены распродажи
            $table->decimal('sale_fixed_price', 10, 2)->nullable()->after('sale_percent');

            // Добавление поля для умножающего множителя зачёркнутой цены
            $table->decimal('strikethrough_multiplier', 8, 2)->nullable()->after('sale_fixed_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Удаление добавленных полей
            $table->dropColumn([
                'sale_type',
                'sale_multiplier',
                'sale_percent',
                'sale_fixed_price',
                'strikethrough_multiplier'
            ]);
        });
    }
}
