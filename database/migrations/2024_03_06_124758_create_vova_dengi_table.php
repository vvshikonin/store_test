<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVovaDengiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vova_dengi', function (Blueprint $table) {
            $table->id();
            $table->integer('upload_index');
            $table->string('date')->nullable();
            $table->string('kontragent')->nullable();
            $table->string('rs1')->nullable();
            $table->string('naimenovanie_banka1')->nullable();
            $table->string('tip_deneg')->nullable();
            $table->string('rs2')->nullable();
            $table->string('naimenovanie_banka2')->nullable();
            $table->string('tip_documenta')->nullable();
            $table->string('nomer_documenta')->nullable();
            $table->string('tip_operacii')->nullable();
            $table->string('opisanie')->nullable();
            $table->string('postuplenie')->nullable();
            $table->string('spisano')->nullable();
            $table->string('komissiya')->nullable();
            $table->string('usn_dohod')->nullable();
            $table->string('usn_rashod')->nullable();
            $table->string('patent')->nullable();
            $table->string('comment')->nullable();
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
        Schema::dropIfExists('vova_dengi');
    }
}
