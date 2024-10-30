<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractorIdColumnToFinancialControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_controls', function (Blueprint $table) {
            $table->unsignedBigInteger('contractor_id')->after('sum');

            $table->foreign('contractor_id')->references('id')->on('contractors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_controls', function (Blueprint $table) {
            //
        });
    }
}
