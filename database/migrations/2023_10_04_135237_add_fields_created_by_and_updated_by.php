<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsCreatedByAndUpdatedBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = [
            'contractor_refunds',
            'defects',
            'financial_controls',
            'inventories',
            'invoices',
            'money_refundables',
            'orders',
            'price_monitorings',
            'products',
            'product_refunds',
            'stocks'
        ];

        foreach ($tableNames as $tableName) {
            if (!Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->bigInteger('created_by')->nullable()->default(null);
                });
            }
    
            if (!Schema::hasColumn($tableName, 'updated_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->bigInteger('updated_by')->nullable()->default(null);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = [
            'financial_controls',
            'inventories',
            'invoices',
            'money_refundables',
            'orders',
            'price_monitorings',
            'products',
            'product_refunds',
            'stocks'
        ];

        foreach ($tableNames as $tableName) {
            if (Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('created_by');
                });
            }
    
            if (Schema::hasColumn($tableName, 'updated_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('updated_by');
                });
            }
        }
    }
}
