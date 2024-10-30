<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\InvoiceProduct;

class ReceivedAtDataMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $invoiceProducts = InvoiceProduct::with(['transactions' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->get();

        foreach($invoiceProducts as $product){
            $product->received_at = $product->transactions->first()?->created_at;
            $product->save();
        }
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
