<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinancialControlsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $financialControlGroupId = DB::table('permission_groups')->insertGetId([
            'name' => 'Контроль расходов'
        ]);

        $permissions = [
            ['name' => 'financial_controls_read', 'label' => 'Просмотр контроля расходов'],
            ['name' => 'financial_controls_create', 'label' => 'Создание транзакций'],
            ['name' => 'financial_controls_update', 'label' => 'Обновление транзакций'],
            ['name' => 'financial_controls_delete', 'label' => 'Удаление транзакций']
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'label' => $permission['label'],
                'permission_group_id' => $financialControlGroupId
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
        $financialControlGroupId = DB::table('permission_groups')
            ->where('name', 'Контроль расходов')
            ->first()
            ->id;

        DB::table('permissions')
            ->where('permission_group_id', $financialControlGroupId)
            ->delete();

        DB::table('permission_groups')
            ->where('id', $financialControlGroupId)
            ->delete();
    }
}
