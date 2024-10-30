<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->unsignedBigInteger('legal_entity_id')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();

            $table->foreign('legal_entity_id')->references('id')->on('legal_entities');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->dropForeign(['legal_entity_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['legal_entity_id', 'payment_method_id']);
        });
    }
}
