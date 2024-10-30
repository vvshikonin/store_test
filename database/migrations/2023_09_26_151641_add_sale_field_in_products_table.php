<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\V1\Stock;

class AddSaleFieldInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_sale')->nullable()->default(null)->after('sku_list');
        });

        $saleStocks = Stock::with(['product'])->where('amount', '>', 0)->where('is_sale', 1)->get();

        foreach($saleStocks as $stock){
            $product = $stock->product;
            $product->is_sale = 1;
            $product->save();
        }

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('is_sale');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
