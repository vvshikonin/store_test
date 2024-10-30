<?php

use App\Models\V1\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSaleHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sale_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('sale_price', 10, 2);
            $table->timestamps();
            $table->dateTime('end_date')->nullable();
        });

        $this->createPromotionRecords();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_sale_history');
    }

        /**
     * Создание записей для существующих товаров по акции.
     *
     * @return void
     */
    protected function createPromotionRecords()
    {
        $products = Product::where('is_sale', 1)->get();

        foreach ($products as $product)
        {
            $discountedPrice = $this->calculateDiscountedPrice($product);

            DB::table('product_sale_history')->insert([
                'product_id' => $product->id,
                'sale_price' => $discountedPrice,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Расчет скидочной цены для товара.
     *
     * @param Product $product
     * @return float
     */
    protected function calculateDiscountedPrice(Product $product)
    {
        switch ($product->sale_type) {
            case 'multiplier':
                return $product->averagePrice * $product->sale_multiplier;
            case 'auto':
            default:
                return $product->salePrice;
        }
    }
}
