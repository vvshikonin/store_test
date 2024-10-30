<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SeedExpenseTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('expense_types')->insert([
            ['name' => 'Продукты', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Вода в офис', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Канцелярия', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Упаковки', 'created_by' => 1, 'updated_by' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('expense_types')->truncate();
    }
}
