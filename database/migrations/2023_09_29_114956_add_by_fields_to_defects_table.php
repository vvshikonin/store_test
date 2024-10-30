<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddByFieldsToDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defects', function (Blueprint $table) {
            // $table->unsignedBigInteger('created_by')->default(1)->after('is_completed'); // добавляем столбец created_by
            // $table->unsignedBigInteger('updated_by')->nullable()->after('created_by'); // добавляем столбец updated_by

            // // Добавляем внешние ключи
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defects', function (Blueprint $table) {
            // Удаляем внешние ключи
            // $table->dropForeign(['created_by']);
            // $table->dropForeign(['updated_by']);

            // $table->dropColumn('created_by'); // удаляем столбец created_by
            // $table->dropColumn('updated_by'); // удаляем столбец updated_by
        });
    }
}
