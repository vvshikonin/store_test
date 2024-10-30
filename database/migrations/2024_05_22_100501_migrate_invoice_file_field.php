<?php

use App\Models\V1\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateInvoiceFileField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function(){
            $invoices = Invoice::whereNotNull('file')->get();
            foreach($invoices as $invoice){
                $invoice->invoice_files = [$invoice->file];
                $invoice->save();
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('file');
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
