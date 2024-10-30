<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlannedDeliveryDateRangeToInvoicePositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_positions', function (Blueprint $table) {
            $table->date('planned_delivery_date_from')->nullable();
            $table->date('planned_delivery_date_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_positions', function (Blueprint $table) {
            $table->dropColumn('planned_delivery_date_from');
            $table->dropColumn('planned_delivery_date_to');
        });
    }
}
