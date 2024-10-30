<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarginalityToBrandsAndContractors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->tinyInteger('marginality')->unsigned()->default(15)->after('name');
        });

        Schema::table('contractors', function (Blueprint $table) {
            $table->tinyInteger('marginality')->unsigned()->default(15)->after('symbolic_code_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('marginality');
        });

        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn('marginality');
        });
    }
}
