<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddContractorsAndExpenseTypesPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Получение ID существующей группы разрешений
        $groupId = DB::table('permission_groups')->where('name', 'Хозяйственные расходы')->value('id');

        // Проверка, существует ли группа
        if ($groupId) {
            $permissions = [
                ['name' => 'contragents_edit', 'label' => 'Редактирование контрагентов'],
                ['name' => 'expense_types_edit', 'label' => 'Редактирование типов расходов']
            ];

            foreach ($permissions as $permission) {
                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'label' => $permission['label'],
                    'permission_group_id' => $groupId
                ]);
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
        // Удаление добавленных разрешений при откате миграции
        $permissions = ['contragents_edit', 'expense_types_edit'];
        DB::table('permissions')->whereIn('name', $permissions)->delete();
    }
}
