<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProductRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_refunds', function (Blueprint $table) {
            $table->foreignId('legal_entity_id')->nullable()->constrained('legal_entities');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_refunds', function (Blueprint $table) {
            //
        });
    }
}
