<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmedSumFieldToCashTransactionablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_transactionables', function (Blueprint $table) {
            $table->decimal('confirmed_sum', 10, 2)->after('is_confirmed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_transactionables', function (Blueprint $table) {
            //
        });
    }
}
