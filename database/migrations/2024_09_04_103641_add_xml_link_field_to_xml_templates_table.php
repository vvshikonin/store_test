<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXmlLinkFieldToXmlTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xml_templates', function (Blueprint $table) {
            $table->string('xml_link')->default(NULL)->nullable()->after('brand_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xml_templates', function (Blueprint $table) {
            $table->dropColumn('xml_link');
        });
    }
}
