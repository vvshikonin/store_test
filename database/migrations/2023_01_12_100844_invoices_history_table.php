<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvoicesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users')->nullable()->default(null);
            $table->integer('old_contractor_id')->nullable()->default(null);
            $table->integer('new_contractor_id')->nullable()->default(null);
            $table->string('old_number')->nullable()->default(null);
            $table->string('new_number')->nullable()->default(null);
            $table->date('old_date')->nullable()->default(null);
            $table->date('new_date')->nullable()->default(null);
            $table->boolean('old_status')->nullable()->default(null);
            $table->boolean('new_status')->nullable()->default(null);
            $table->date('old_planed_delivery_date')->nullable()->default(null);
            $table->date('new_planed_delivery_date')->nullable()->default(null);
            $table->string('old_comment')->nullable()->default(null);
            $table->string('new_comment')->nullable()->default(null);
            $table->boolean('old_delivery_type')->nullable()->default(null);
            $table->boolean('new_delivery_type')->nullable()->default(null);
            $table->boolean('old_payment_type')->nullable()->default(null);
            $table->boolean('new_payment_type')->nullable()->default(null);
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
        //
    }
}
