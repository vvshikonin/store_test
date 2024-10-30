<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\Product;
use Illuminate\Support\Facades\DB;

class CheckOldProductsForSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:check-old-for-sale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет дату последней продажи товаров и выставляет их на распродажу, если дата последней продажи больше 21 дня';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info('Запуск проверки товаров для распродажи по давности последней продажи...');
        \Log::info('Дата последней продажи должна быть раньше: ' . now()->subDays(21)->format('Y-m-d'));

        // Подзапрос для суммы stocks.amount
        $stocksSubquery = DB::table('stocks')
            ->select('product_id', DB::raw('SUM(amount) as total_stock_amount'))
            ->whereNotNull('last_saled_date')
            ->where('last_saled_date', '<=', now()->subDays(21))
            ->groupBy('product_id');

        // Подзапрос для суммы order_products.amount только для заказов со статусом 'reserved'
        $reservedOrderProductsSubquery = DB::table('order_products')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->select('order_products.product_id', DB::raw('SUM(order_products.amount) as total_reserved_amount'))
            ->where('orders.state', 'reserved')
            ->groupBy('order_products.product_id');

        // Основной запрос
        $productsForSale = Product::joinSub($stocksSubquery, 'stocks_sum', function ($join) {
                $join->on('products.id', '=', 'stocks_sum.product_id');
            })
            ->leftJoinSub($reservedOrderProductsSubquery, 'reserved_sum', function ($join) {
                $join->on('products.id', '=', 'reserved_sum.product_id');
            })
            ->whereNull('products.maintained_balance')
            ->where('products.is_sale', 0)
            ->whereNull('products.deleted_at')
            ->whereRaw('stocks_sum.total_stock_amount - COALESCE(reserved_sum.total_reserved_amount, 0) > 0')
            ->select('products.*')
            ->get();

        $productsForUnsale = Product::joinSub($stocksSubquery, 'stocks_sum', function ($join) {
                $join->on('products.id', '=', 'stocks_sum.product_id');
            })
            ->leftJoinSub($reservedOrderProductsSubquery, 'reserved_sum', function ($join) {
                $join->on('products.id', '=', 'reserved_sum.product_id');
            })
            ->whereNull('products.maintained_balance')
            ->where('products.is_sale', 1)
            ->where('products.sale_type', 'multiplier')
            ->where('products.sale_multiplier', 1.05)
            ->whereNull('products.deleted_at')
            ->whereRaw('stocks_sum.total_stock_amount - COALESCE(reserved_sum.total_reserved_amount, 0) <= 0')
            ->select('products.*')
            ->get();

        \Log::info('Найдено ' . $productsForSale->count() . ' товаров для распродажи.');
        \Log::info('Найдено ' . $productsForUnsale->count() . ' товаров для снятия с распродажи.');

        foreach ($productsForSale as $product)
        {
            $product->is_sale = 1;
            $product->sale_type = 'multiplier';
            $product->sale_multiplier = 1.05;
            $product->save();
        }

        foreach ($productsForUnsale as $product)
        {
            $product->is_sale = 0;
            $product->sale_type = 'auto';
            $product->sale_multiplier = null;
            $product->save();
        }

        \Log::info('Проверка товаров для распродажи по давности последней продажи завершена.');

        return 0;
    }
}