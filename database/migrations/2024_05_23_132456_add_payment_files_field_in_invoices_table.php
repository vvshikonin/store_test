<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\Invoice;

class AddPaymentFilesFieldInInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('payment_files')->after('receipt_file')->nullable()->default(null);
        });

        DB::transaction(function(){
            $invoices = Invoice::whereNotNull('receipt_file')->get();
            foreach($invoices as $invoice){
                $invoice->payment_files = [$invoice->receipt_file];
                $invoice->save();
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('receipt_file');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
}
