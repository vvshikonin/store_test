<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetIsSaleDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Устанавливаем всем существующим записям значение is_sale в false
        DB::table('products')->update(['is_sale' => false]);

        // Изменяем структуру таблицы
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_sale')->default(false)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Возвращаем возможность поля быть nullable
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_sale')->nullable()->change();
        });
    }
}
