<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateExpensesPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $expensesGroupId = DB::table('permission_groups')->insertGetId([
            'name' => 'Хозяйственные расходы'
        ]);

        $permissions = [
            ['name' => 'expenses_read', 'label' => 'Просмотр хоз. расходов'],
            ['name' => 'expenses_create', 'label' => 'Создание хоз. расходов'],
            ['name' => 'expenses_update', 'label' => 'Редактирование хоз. расходов'],
            ['name' => 'expenses_delete', 'label' => 'Удаление хоз. расходов']
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'label' => $permission['label'],
                'permission_group_id' => $expensesGroupId
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
        // Удалите созданные разрешения и группу разрешений при откате миграции.
        $permissions = ['expenses_read', 'expenses_create', 'expenses_update', 'expenses_delete'];
        DB::table('permissions')->whereIn('name', $permissions)->delete();
        DB::table('permission_groups')->where('name', 'Хозяйственные расходы')->delete();
    }
}
