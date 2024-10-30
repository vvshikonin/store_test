<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefectStatusAndCrmFieldsToOrderPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->string('defect_status')->nullable()->default(null);
            $table->string('crm_fields')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_positions', function (Blueprint $table) {
            //
        });
    }
}
