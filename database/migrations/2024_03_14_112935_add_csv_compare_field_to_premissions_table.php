<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\Permission;

class AddCsvCompareFieldToPremissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $newPermission = new Permission();
        $newPermission->name = 'csv-compare';
        $newPermission->label = 'CSV Compare';
        $newPermission->permission_group_id = 8;
        $newPermission->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = Permission::where('name', 'csv-compare')->first();
        $permission->delete();
    }
}
