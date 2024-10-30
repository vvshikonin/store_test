<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderStatusesAndOrderStatusGroupsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_status_groups', function (Blueprint $table) {
            $table->id();
            $table->string('symbolic_code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('symbolic_code')->unique();
            $table->string('name');
            $table->foreignId('status_group_id')->constrained('order_status_groups')->onDelete('cascade')->nullable()->default(null);
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
        Schema::dropIfExists('order_statuses');
        Schema::dropIfExists('order_status_groups');
    }
}
