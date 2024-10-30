<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFilterTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_filter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->json('template_data');
            $table->enum('table', [
                'products', 
                'invoices', 
                'price_monitoring', 
                'inventories', 
                'financial_controls', 
                'product_refunds', 
                'contractor_refunds', 
                'defects',
                'money_refunds',
                'brands',
                'contractors'
            ]);
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
        Schema::dropIfExists('user_filter_templates');
    }
}
