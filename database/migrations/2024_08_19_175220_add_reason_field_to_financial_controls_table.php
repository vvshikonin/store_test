<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonFieldToFinancialControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_controls', function (Blueprint $table) {
            $table->text('reason')->nullable()->default(NULL)->comment('Основание');
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
            $table->dropColumn('reason');
        });
    }
}
