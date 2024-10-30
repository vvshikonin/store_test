<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('contractors');
            $table->string('number');
            $table->date('date');
            $table->integer('status')->default(0);
            $table->date('planed_delivery_date')->nullable()->default(null);
            $table->string('comment')->nullable()->default(null);
            $table->integer('delivery_type')->nullable()->default(null);
            $table->integer('payment_type')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
