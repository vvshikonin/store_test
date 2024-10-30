<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TransactionableTypeMorphStockInTransactionablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('transactionables')
            ->where('transactionable_type', 'Stock')
            ->update(['transactionable_type' => 'App\Models\V1\Stock']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('transactionables')
            ->where('transactionable_type', 'App\Models\V1\Stock')
            ->update(['transactionable_type' => 'Stock']);
    }
}
