<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewObjectsToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            ['name' => 'all_expenses_create', 'label' => 'Создание любых хоз. расходов'],
            ['name' => 'fast_expenses_create', 'label' => 'Создание быстрых хоз. расходов'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'label' => $permission['label'],
                'permission_group_id' => 14
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = ['all_expenses_create', 'fast_expenses_create'];
        DB::table('permissions')->whereIn('name', $permissions)->delete();
    }
}
