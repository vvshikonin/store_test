<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetIsMainContractorFieldToContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('contractors')->update(['is_main_contractor' => 1]);
        DB::table('contractors')
            ->where('name', 'like', '*Доп%')
            ->update(['is_main_contractor' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractors', function (Blueprint $table) {
            //
        });
    }
}
