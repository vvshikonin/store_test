<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPermissionsToPerrmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permission_groups')->insert(['name' => 'Возвраты поставщику']);

        $newPermissionGroupId = DB::getPdo()->lastInsertId();

        DB::table('permissions')->insert([
            ['name' => 'contractor_refund_read', 'label' => 'Просмотр возвратов поставщику', 'permission_group_id' => $newPermissionGroupId],
            ['name' => 'contractor_refund_update', 'label' => 'Редактирование возвратов поставщику', 'permission_group_id' => $newPermissionGroupId],
            ['name' => 'contractor_refund_create', 'label' => 'Создание возвратов поставщику', 'permission_group_id' => $newPermissionGroupId],
            ['name' => 'contractor_refund_delete', 'label' => 'Удаление возвратов поставщику', 'permission_group_id' => $newPermissionGroupId]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->whereIn('name', ['contractor_refund_read', 'contractor_refund_update', 'contractor_refund_create', 'contractor_refund_delete'])->delete();

        DB::table('permission_groups')->where('name', 'Возвраты поставщику')->delete();
    }
}
