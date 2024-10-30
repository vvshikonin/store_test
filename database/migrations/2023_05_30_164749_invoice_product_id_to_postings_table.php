<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvoiceProductIdToPostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('postings', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_product_id')->nullable();
        });

        DB::table('postings')->update([
            'invoice_product_id' => DB::raw('invoice_position_id')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('postings', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_position_id')->nullable();

            DB::table('postings')->update([
                'invoice_position_id' => DB::raw('invoice_product_id')
            ]);

            $table->dropColumn('invoice_product_id');
        });
    }
}
