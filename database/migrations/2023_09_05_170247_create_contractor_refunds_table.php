<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->boolean('is_complete')->default(0);
            $table->string('refund_documents')->nullable();
            $table->string('comment')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_address')->nullable();
            $table->enum('delivery_status', ['complete', 'at_courier', 'in_tc'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
            // $table->bigInteger('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_refunds');
    }
}
