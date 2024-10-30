<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetBrandIdFieldInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $brands = DB::table('brands')->get();
            
            DB::table('products')->orderBy('id')->chunk(200, function ($products) use ($brands) {
                foreach ($products as $product) {
                    foreach ($brands as $brand) {
                        if (stripos($product->name, $brand->name) !== false) {
                            DB::table('products')
                                ->where('id', $product->id)
                                ->update(['brand_id' => $brand->id]);
                            break;
                        }
                    }
                }
            });
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
