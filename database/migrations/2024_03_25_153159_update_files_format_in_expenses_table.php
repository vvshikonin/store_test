<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFilesFormatInExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Получаем все записи с файлами в старом формате
        $expenses = DB::table('expenses')->where('files', 'like', '{"expenses/%')->get();

        foreach ($expenses as $expense) {
            // Преобразуем строку JSON в массив
            $files = json_decode($expense->files, true);

            // Преобразование старого формата в новый
            if ($files) {
                foreach ($files as $oldPath => $name) {
                    $newFormat = json_encode(['path' => $oldPath, 'name' => $name]);

                    // Обновляем запись в базе данных
                    DB::table('expenses')
                        ->where('id', $expense->id)
                        ->update(['files' => $newFormat]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
        });
    }
}
