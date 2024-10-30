<?php

namespace App\Http\Controllers\API\V1;

use App\Casts\TransactionableTypeName;
use Gate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\Product;
use App\Http\Resources\V1\Products\ProductResource;
use App\Http\Resources\V1\Products\ProductIndexCollection;
use App\Models\V1\OrderPosition;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductsExport;
use App\Exports\ProductTransactionsExport;
use App\Exports\StorePositionsExport;
use App\Http\Resources\V1\Products\ProductIndexResource;
use App\Models\V1\InventoryProduct;
use App\Models\V1\InvoiceProduct;
use App\Models\V1\Stock;
use App\Models\V1\OrderProduct;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Product\SearchProductRequest;
use App\Models\V1\Transactionable;
use App\Services\TransactionService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected function products_filter(Request $request, $products)
    {
        $sort_type = $request->get('sort_type');
        $sort_field = $request->get('sort_field');
        $product_filter = $request->get('product_filter');
        // $brand_filter = $request->get('brand_filter');
        $price_from_filter = $request->get('price_from_filter');
        $price_to_filter = $request->get('price_to_filter');
        $price_equal_filter = $request->get('price_equal_filter');
        $price_notEqual_filter = $request->get('price_notEqual_filter');

        $avg_price_from_filter = $request->get('avg_price_from_filter');
        $avg_price_to_filter = $request->get('avg_price_to_filter');
        $avg_price_equal_filter = $request->get('avg_price_equal_filter');
        $avg_price_notEqual_filter = $request->get('avg_price_notEqual_filter');

        $stock_count_from_filter = $request->get('stock_count_from_filter');
        $stock_count_to_filter = $request->get('stock_count_to_filter');
        $stock_count_equal_filter = $request->get('stock_count_equal_filter');
        $stock_count_notEqual_filter = $request->get('stock_count_notEqual_filter');

        $comment_filter = $request->get('comment_filter');
        $order_filter = $request->get('order_filter');
        $updated_at_start_filter = $request->get('updated_at_start_filter');
        $updated_at_end_filter = $request->get('updated_at_end_filter');
        $updated_at_equal_filter = $request->get('updated_at_equal_filter');
        $updated_at_notEqual_filter = $request->get('updated_at_notEqual_filter');
        $has_sale = $request->boolean('has_sale');
        $has_expected = $request->boolean('has_expected');
        $has_warning = $request->boolean('has_warning');
        $maintained_balance_state = $request->get('maintained_balance_state');
        $has_orders = $request->boolean('has_orders');
        $has_real_stock = $request->boolean('has_real_stock');
        $has_free_balance = $request->boolean('has_free_balance');
        $is_profitable_purchase = $request->boolean('is_profitable_purchase');
        $contractors_filter = $request->get('contractors_filter');
        $delivery_date_start_filter = $request->get('delivery_date_start_filter');
        $delivery_date_end_filter = $request->get('delivery_date_end_filter');
        $delivery_date_equal_filter = $request->get('delivery_date_equal_filter');
        $delivery_date_notEqual_filter = $request->get('delivery_date_notEqual_filter');

        $brand_filter = $request->get('brand_filter');
        $sku_filter = $request->get('sku_filter');

        // filters
        if ($product_filter) {
            $products->productFilter($product_filter);
        }
        if ($brand_filter) {
            $products->brandFilter($brand_filter);
        }
        if ($comment_filter) {
            $products->commentFilter($comment_filter);
        }
        if ($order_filter) {
            $products->orderFilter($order_filter);
        }
        if ($sku_filter !== null) {
            $products->skuFilter($sku_filter);
        }
        if ($contractors_filter) {
            $products->contractorsFilter($contractors_filter);
        }
        if ($price_from_filter || $price_to_filter || $price_equal_filter || $price_notEqual_filter) {
            $products->productPriceFilter($price_from_filter, $price_to_filter, $price_equal_filter, $price_notEqual_filter);
        }

        if ($avg_price_from_filter || $avg_price_to_filter || $avg_price_equal_filter || $avg_price_notEqual_filter) {
            $products->productAvgPriceFilter($avg_price_from_filter, $avg_price_to_filter, $avg_price_equal_filter, $avg_price_notEqual_filter);
        }

        if ($stock_count_from_filter || $stock_count_to_filter || $stock_count_equal_filter || $stock_count_notEqual_filter) {
            $products->stockCountFilter($stock_count_from_filter, $stock_count_to_filter, $stock_count_equal_filter, $stock_count_notEqual_filter);
        }

        if ($updated_at_start_filter || $updated_at_end_filter || $updated_at_equal_filter || $updated_at_notEqual_filter) {
            $products->productUpdateDateFilter($updated_at_start_filter, $updated_at_end_filter, $updated_at_equal_filter, $updated_at_notEqual_filter);
        }
        if ($delivery_date_start_filter || $delivery_date_end_filter || $delivery_date_equal_filter || $delivery_date_notEqual_filter) {
            $products->productDeliveryDateFilter($delivery_date_start_filter, $delivery_date_end_filter, $delivery_date_equal_filter, $delivery_date_notEqual_filter);
        }
        if ($has_sale) {
            $products->hasSale();
        }
        if ($has_expected) {
            $products->hasExpected();
        }
        if ($has_warning) {
            $products->hasWarning();
        }
        if ($maintained_balance_state) {
            $products->maintainedBalanceState($maintained_balance_state);
        }
        if ($has_orders) {
            $products->hasOrders();
        }
        if ($has_real_stock) {
            $products->hasRealStock();
        }
        if ($has_free_balance) {
            $products->hasFreeStock();
        }
        if ($is_profitable_purchase) {
            $products->isProfitablePurchase();
        }
        if ($sort_field && $sort_type) {
            $products = $products->orderBy($sort_field, $sort_type);
        }

        if ($request->has('payment_status')) {
            $products->paymentStatus($request->input('payment_status'));
        }

        return $products;
    }

    protected function products_select()
    {
        $order_position_sub_query = OrderProduct::selectRaw('SUM(amount) AS reserved')
            ->groupBy('product_id')
            ->where('orders.state', 'reserved')
            ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
            ->selectRaw('product_id')
            ->selectRaw('GROUP_CONCAT(CONCAT(orders.number, " - ", amount, " шт.") SEPARATOR "; ") AS orders_info');


        $invoice_position_sub_query = InvoiceProduct::selectRaw(
            'SUM(amount - received - refused) AS received,
            SUM(amount - received - refused) AS expected'
        )
            ->groupBy('product_id')
            ->selectRaw('product_id');

        // Подзапрос для получения минимальной цены с учетом Transactionable
        $min_price_sub_query = Stock::select('stocks.product_id', DB::raw('MIN(price) as min_price'))
            ->where('stocks.amount', '>', 0)
            ->where('stocks.price', '>', 1)
            ->whereNull('stocks.deleted_at')
            ->groupBy('stocks.product_id');

        // Подзапрос для получения даты закупки с минимальной ценой и учетом Transactionable
        $min_price_date_sub_query = Stock::select('stocks.product_id', DB::raw('MIN(stocks.created_at) as min_price_date'))
            ->joinSub($min_price_sub_query, 'mp', function ($join) {
                $join->on('stocks.product_id', '=', 'mp.product_id')
                    ->whereColumn('stocks.price', '=', 'mp.min_price');
            })
            ->groupBy('stocks.product_id');

        // Новый Подзапрос для минимальной цены за все время с учетом Transactionable
        $min_price_all_time_sub_query = Stock::select('stocks.product_id', DB::raw('MIN(price) as min_price_all_time'))
            ->where('stocks.price', '>', 1)
            ->whereNull('stocks.deleted_at')
            ->groupBy('stocks.product_id');

        // Подзапрос для получения даты закупки с минимальной ценой за все время и учетом Transactionable
        $min_price_all_time_date_sub_query = Stock::select('stocks.product_id', DB::raw('MIN(stocks.created_at) as min_price_all_time_date'))
            ->joinSub($min_price_all_time_sub_query, 'mpat', function ($join) {
                $join->on('stocks.product_id', '=', 'mpat.product_id')
                    ->whereColumn('stocks.price', '=', 'mpat.min_price_all_time');
            })
            ->groupBy('stocks.product_id');

        $store_position_sub_query = Stock::selectRaw('GROUP_CONCAT(DISTINCT contractors.name SEPARATOR ", ") AS contractors_name')
            ->selectRaw('stocks.product_id')
            ->selectRaw('(SUM(price * CASE WHEN price = 0 THEN 0 ELSE amount END) / SUM(CASE WHEN price = 0 THEN 0 ELSE amount END)) AS avg_price')
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw('invoice_products.expected AS expected')
            ->selectRaw('mp.min_price AS min_price')
            ->selectRaw('mpd.min_price_date AS min_price_date')
            ->selectRaw('mpat.min_price_all_time AS min_price_all_time')
            ->selectRaw('mpatd.min_price_all_time_date AS min_price_all_time_date')
            ->selectRaw('SUM(invoice_products.received) AS received')
            ->selectRaw('SUM(saled) AS saled')
            ->selectRaw('SUM(amount * price) AS total_sum')
            ->selectRaw('SUM(CASE WHEN stocks.is_profitable_purchase = 1 THEN amount * price ELSE 0 END) AS profitable_sum')
            ->leftJoinSub($invoice_position_sub_query, 'invoice_products', function ($join) {
                $join->on('stocks.product_id', '=', 'invoice_products.product_id');
            })
            ->leftJoinSub($min_price_sub_query, 'mp', function ($join) {
                $join->on('stocks.product_id', '=', 'mp.product_id');
            })
            ->leftJoinSub($min_price_date_sub_query, 'mpd', function ($join) {
                $join->on('stocks.product_id', '=', 'mpd.product_id');
            })
            ->leftJoinSub($min_price_all_time_sub_query, 'mpat', function ($join) {
                $join->on('stocks.product_id', '=', 'mpat.product_id');
            })
            ->leftJoinSub($min_price_all_time_date_sub_query, 'mpatd', function ($join) {
                $join->on('stocks.product_id', '=', 'mpatd.product_id');
            })
            ->leftJoin('contractors', 'stocks.contractor_id', '=', 'contractors.id')
            ->groupBy('stocks.product_id');

        $products = Product::select(
            'products.id',
            'products.main_sku',
            'products.name',
            'products.maintained_balance',
            'products.updated_at',
            'products.sku_list',
            'products.is_sale',
            'products.sale_type',
            'products.sale_multiplier',
            'brands.name as brand_name',
            'stocks.contractors_name',
            'stocks.avg_price',
            'stocks.amount',
            'stocks.min_price',
            'stocks.min_price_date',
            'stocks.min_price_all_time',
            'stocks.min_price_all_time_date',

            DB::raw('IF (stocks.received is null, 0, stocks.received) as received'),
            'stocks.total_sum',
            'stocks.profitable_sum',

            DB::raw('IF (order_products.reserved is null, 0, order_products.reserved) as reserved'),
            'stocks.saled',

            DB::raw('(IF (stocks.amount is null, 0, stocks.amount) -
            IF (order_products.reserved is null, 0, order_products.reserved)) as free_stock'),
            DB::raw('IF (stocks.expected is null, 0, stocks.expected) as expected'),
            DB::raw('IF (order_products.orders_info IS NULL, "", order_products.orders_info) as orders_info')
        )

            ->leftJoinSub($store_position_sub_query, 'stocks', function ($join) {
                $join->on('products.id', '=', 'stocks.product_id');
            })
            ->leftJoinSub($order_position_sub_query, 'order_products', function ($join) {
                $join->on('products.id', '=', 'order_products.product_id');
            })
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->joinInvoiceProducts();

        return $products;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->products_select();
        $products = $this->products_filter($request, $products);

        $per_page = $request->get('per_page');

        $total_sum = $products->sum('total_sum');
        $total_profitable_sum = $products->sum('profitable_sum');
        $total_stocks = $products->sum('amount');
        $total_maintained_balance = $products->sum('maintained_balance');
        $total_free_stocks = $products->get()->sum('free_stock');
        $total_reserved_sum = $products->sum(DB::raw('reserved * avg_price'));

        // $total_free_stocks = $products->sum('free_stock');
        if ($per_page) {
            $products = $products->paginate($per_page);
        } else {
            $products = $products->paginate(15);
        }

        $products->load(['stocks' => function ($query) use ($request) {
            if ($request->boolean('has_real_stock')) {
                $query->where('stocks.amount', '>', 0);
            }
        }]);

        return new ProductIndexCollection($products, $total_sum, $total_stocks, $total_free_stocks, $total_reserved_sum, $total_maintained_balance, $total_profitable_sum);
        // return ProductIndexResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $product = new Product;

        $product = $product->fill($request->all());

        $sku_list = [];

        if ($request->sku_list) {
            $sku_list = $request->sku_list;
        } else {
            array_push($sku_list, $product->main_sku);
        }

        if (($key = array_search($product->main_sku, $sku_list)) !== false) {
            unset($sku_list[$key]);
            array_unshift($sku_list, $product->main_sku);
        }

        $product->sku_list = json_encode($sku_list);

        $product->save();

        return new ProductResource($product->refresh());
    }

    public function bulkStore(Request $request)
    {
        $store_requests = $request->get('store_requests');
        foreach ($store_requests as $store_request) {
            $this->store($store_request);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Подзапрос для вычисления средне-взвешанной цены
        $invoice_position_sub_query = InvoiceProduct::selectRaw(
            'SUM(amount - received - refused) AS received,
            SUM(amount - received - refused) AS expected'
        )
            ->groupBy('product_id')
            ->selectRaw('product_id');

        $store_position_sub_query = Stock::selectRaw('GROUP_CONCAT(DISTINCT contractors.name SEPARATOR ", ") AS contractors_name')
            ->selectRaw('stocks.product_id')
            ->selectRaw('(SUM(price * CASE WHEN price = 0 THEN 0 ELSE amount END) / SUM(CASE WHEN price = 0 THEN 0 ELSE amount END)) AS avg_price')
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw('invoice_products.expected AS expected')
            ->selectRaw('SUM(invoice_products.received) AS received')
            ->selectRaw('SUM(saled) AS saled')
            ->selectRaw('SUM(amount * price) AS total_sum')
            ->leftJoinSub($invoice_position_sub_query, 'invoice_products', function ($join) {
                $join->on('stocks.product_id', '=', 'invoice_products.product_id');
            })
            ->leftJoin('contractors', 'stocks.contractor_id', '=', 'contractors.id')
            ->groupBy('stocks.product_id');

        // Основной запрос для получения продукта с вычисленной avg_price
        $product = Product::select(
            'products.*',
            'stocks.avg_price'
        )
            ->leftJoinSub($store_position_sub_query, 'stocks', function ($join) {
                $join->on('products.id', '=', 'stocks.product_id');
            })
            ->with(['brand', 'stocks', 'creator', 'updater'])
            ->find($id);

        return new ProductResource($product);
    }

    /**
     * Составяляет запрос для получения транзакций
     *
     * @return $transactions
     */
    protected function getTransactionsIndex($product)
    {
        $stockIds = $product->stocks->pluck('id');
        $transactions = Transactionable::whereIn('stock_id', $stockIds)
            ->leftJoin('users', 'users.id', '=', 'transactionables.user_id')
            ->leftJoin('stocks', 'stocks.id', '=', 'transactionables.stock_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'stocks.contractor_id')
            ->select('transactionables.*', 'users.name as user_name', 'contractors.name as stock_contractor_name', 'stocks.price as stock_price')
            ->orderBy('transactionables.created_at', 'desc');

        return $transactions;
    }

    protected function getCalculatedStocks($transactions, $stockIds)
    {
        foreach ($transactions as $transaction) {
            $previousTransactionsForStock = $transactions->where('stock_id', $transaction->stock_id)
                ->where('created_at', '<=', $transaction->created_at);

            $previousTrasactionsIn = $previousTransactionsForStock->where('type', 'In')->sum('amount');
            $previousTrasactionsOut = $previousTransactionsForStock->where('type', 'Out')->sum('amount');
            $amountAtTransactionForStock = $previousTrasactionsIn - $previousTrasactionsOut;

            $transaction->amount_at_transaction = $amountAtTransactionForStock;

            $previousTransactionsForProduct = $transactions->whereIn('stock_id', $stockIds)
                ->where('created_at', '<=', $transaction->created_at);

            $previousTrasactionsForProductIn = $previousTransactionsForProduct->where('type', 'In')->sum('amount');
            $previousTrasactionsForProductOut = $previousTransactionsForProduct->where('type', 'Out')->sum('amount');
            $amountAtTransactionForProduct = $previousTrasactionsForProductIn - $previousTrasactionsForProductOut;

            $transaction->total_amount_at_transaction = $amountAtTransactionForProduct;
        }

        return $transactions;
    }

    /**
     * Получения транзакций на остатки, связанных с этим товаром
     *
     * @param App\Models\V1\Product $product
     * @return $transactions
     */
    public function getTransactions(Product $product)
    {
        $transactions = $this->getTransactionsIndex($product);
        $stockIds = $product->stocks->pluck('id');

        $transactions = $transactions->get()
            ->loadDeepRelations(['order', 'invoice', 'inventory']);

        $transactions = $this->getCalculatedStocks($transactions, $stockIds);

        return $transactions;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id)->refresh();

        $this->authorize('update', $product);

        $product->fill($request->all());

        if ($request->sale_type === 'nonsale')
            $product->is_sale = 0;

        if ($request->has('sku_list')) {
            $sku_list = [];

            if ($request->sku_list) {
                $sku_list = $request->sku_list;
            } else {
                array_push($sku_list, $product->main_sku);
            }

            if (($key = array_search($product->main_sku, $sku_list)) !== false) {
                unset($sku_list[$key]);
                array_unshift($sku_list, $product->main_sku);
            }

            $product->sku_list = $sku_list;
        }

        $product->save();

        return $product->refresh();
    }

    public function checkSku(Request $request)
    {
        $new_sku = strtolower($request->get('sku'));

        $product = Product::whereRaw("JSON_SEARCH(LOWER(CAST(sku_list AS CHAR)), 'one', ?) IS NOT NULL", ["{$new_sku}"])->first();

        if ($product) {
            return $product;
        } else {
            return null;
        }
    }

    public function merge(Request $request)
    {
        $this->authorize('merge', Product::class);

        $new_product = new Product;

        DB::transaction(function () use (&$new_product, $request) {
            $a_product_id = $request->a_product_id;
            $b_product_id = $request->b_product_id;

            // Получаем объекты товаров
            $a_product = Product::find($a_product_id);
            $b_product = Product::find($b_product_id);

            // Объединяем sku_list обоих товаров и удаляем дубликаты
            $a_sku_list = json_decode($a_product->sku_list, true);
            $b_sku_list = json_decode($b_product->sku_list, true);
            $merged_sku_list = array_unique(array_merge($a_sku_list, $b_sku_list));

            // Складываем maintained_balance обоих товаров
            $maintained_balance_sum = null;
            $maintained_balance_sum += $a_product->maintained_balance;
            $maintained_balance_sum += $b_product->maintained_balance;

            // Создаем новый товар и копируем необходимые свойства из a_product и b_product
            $new_product->name = $a_product->name;

            $new_product->brand_id = $a_product->brand_id ? $a_product->brand_id : $b_product->brand_id;

            $new_product->maintained_balance = $maintained_balance_sum;
            $new_product->sku_list = json_encode($merged_sku_list);
            $new_product->main_sku = $a_product->main_sku;

            // Сохраняем новый товар в базе данных
            $new_product->save();

            // Обновляем product_id во всех связанных StorePosition записях

            Stock::where('product_id', $a_product_id)
                ->orWhere('product_id', $b_product_id)
                ->update(['product_id' => $new_product->id]);

            // Обновляем product_id во всех связанных OrderProducts записях
            OrderProduct::withTrashed()
                ->where('product_id', $a_product_id)
                ->orWhere('product_id', $b_product_id)
                ->update(['product_id' => $new_product->id]);

            InventoryProduct::where('product_id', $a_product_id)
                ->orWhere('product_id', $b_product_id)
                ->update(['product_id' => $new_product->id]);

            InvoiceProduct::where('product_id', $a_product_id)
                ->orWhere('product_id', $b_product_id)
                ->update(['product_id' => $new_product->id]);

            // Удаляем товары a_product и b_product из базы данных
            $a_product->delete();
            $b_product->delete();
        });
        return new ProductResource($new_product->refresh());
    }

    public function products_export(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->products_select();
        $products = $this->products_filter($request, $products);

        $products = $products->get();

        return Excel::download(new ProductsExport($products), 'products.xlsx');
    }

    public function store_positions_export(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products_filter_sub_query = Product::select('products.id as sub_filter_product_id');
        // $products_filter_sub_query = $this->products_filter($request, $products_filter_sub_query);

        $product_filter = $request->get('product_filter');
        $brand_filter = $request->get('brand_filter');
        $price_from_filter = $request->get('price_from_filter');
        $price_to_filter = $request->get('price_to_filter');
        $price_equal_filter = $request->get('price_equal_filter');
        $price_notEqual_filter = $request->get('price_notEqual_filter');
        $comment_filter = $request->get('comment_filter');
        $order_filter = $request->get('order_filter');
        $updated_at_start_filter = $request->get('updated_at_start_filter');
        $updated_at_end_filter = $request->get('updated_at_end_filter');
        $updated_at_equal_filter = $request->get('updated_at_equal_filter');
        $updated_at_notEqual_filter = $request->get('updated_at_notEqual_filter');
        $has_sale = $request->boolean('has_sale');
        $has_expected = $request->boolean('has_expected');
        $has_warning = $request->boolean('has_warning');
        $has_maintained_balance = $request->boolean('has_maintained_balance');
        $has_orders = $request->boolean('has_orders');
        $has_real_stock = $request->boolean('has_real_stock');
        $has_free_balance = $request->boolean('has_free_balance');
        $contractors_filter = $request->get('contractors_filter');
        $delivery_date_start_filter = $request->get('delivery_date_start_filter');
        $delivery_date_end_filter = $request->get('delivery_date_end_filter');
        $delivery_date_equal_filter = $request->get('delivery_date_equal_filter');
        $delivery_date_notEqual_filter = $request->get('delivery_date_notEqual_filter');

        $brand_filter = $request->get('brand_filter');
        $sku_filter = $request->get('sku_filter');

        // filters
        if ($product_filter) {
            $products_filter_sub_query->productFilter($product_filter);
        }
        if ($brand_filter) {
            $products_filter_sub_query->brandFilter($brand_filter);
        }
        if ($comment_filter) {
            $products_filter_sub_query->commentFilter($comment_filter);
        }
        if ($order_filter) {
            $products_filter_sub_query->orderFilter($order_filter);
        }
        if ($sku_filter !== null) {
            $products_filter_sub_query->skuFilter($sku_filter);
        }
        if ($contractors_filter) {
            $products_filter_sub_query->contractorsFilter($contractors_filter);
        }
        if ($price_from_filter || $price_to_filter || $price_equal_filter || $price_notEqual_filter) {
            $products_filter_sub_query->productPriceFilter($price_from_filter, $price_to_filter, $price_equal_filter, $price_notEqual_filter);
        }
        if ($updated_at_start_filter || $updated_at_end_filter || $updated_at_equal_filter || $updated_at_notEqual_filter) {
            $products_filter_sub_query->productUpdateDateFilter($updated_at_start_filter, $updated_at_end_filter, $updated_at_equal_filter, $updated_at_notEqual_filter);
        }
        if ($delivery_date_start_filter || $delivery_date_end_filter || $delivery_date_equal_filter || $delivery_date_notEqual_filter) {
            $products_filter_sub_query->productDeliveryDateFilter($delivery_date_start_filter, $delivery_date_end_filter, $delivery_date_equal_filter, $delivery_date_notEqual_filter);
        }
        if ($has_sale) {
            $products_filter_sub_query->hasSale();
        }
        if ($has_expected) {
            $products_filter_sub_query->hasExpected();
        }
        if ($has_warning) {
            $products_filter_sub_query->hasWarning();
        }
        if ($has_maintained_balance) {
            $products_filter_sub_query->hasMainteinedBalance();
        }
        if ($has_orders) {
            $products_filter_sub_query->hasOrders();
        }
        if ($has_real_stock) {
            $products_filter_sub_query->hasRealStock();
        }
        if ($has_free_balance) {
            $products_filter_sub_query->hasFreeStock();
        }

        if ($request->has('payment_status')) {
            $products_filter_sub_query->paymentStatus($request->input('payment_status'));
        }

        $products_sub_query = Product::select('products.id as sub_product_id', 'products.name', 'products.is_sale', 'products.maintained_balance', 'products.main_sku', 'products.sku_list', 'brands.name as brand_name')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id');

        $store_positions = Stock::select(
            'stocks.id',
            'stocks.product_id',
            'stocks.contractor_id',
            'stocks.price',
            'stocks.amount',
            'stocks.is_profitable_purchase',
            'stocks.saled',
            'stocks.user_comment',
            'stocks.last_receive_date',
            'products.is_sale',
            'products.name',
            'products.maintained_balance',
            'products.main_sku',
            'products.sku_list',
            'products.brand_name',
            'contractors.name as contractor_name',
            DB::raw('SUM(invoice_products.amount) - SUM(invoice_products.received) - SUM(invoice_products.refused) as expected')
        )
            ->whereIn('stocks.product_id', $products_filter_sub_query)
            ->leftJoinSub($products_sub_query, 'products', function ($join) {
                $join->on('products.sub_product_id', '=', 'stocks.product_id');
            })
            ->leftJoin('contractors', 'stocks.contractor_id', '=', 'contractors.id')
            ->leftJoin('invoice_products', function ($join) {
                $join->on('invoice_products.product_id', '=', 'stocks.product_id')
                    ->join('invoices', 'invoices.id', '=', 'invoice_products.invoice_id')
                    ->where('invoice_products.price', '=', 'stocks.price')
                    ->where('invoices.contractor_id', '=', 'stocks.contractor_id');
            })
            ->groupBy('stocks.id')
            ->get();

        return Excel::download(new StorePositionsExport($store_positions), 'StorePositions.xlsx');
    }

    public function transactionsExport(Product $product)
    {
        $transactions = $this->getTransactionsIndex($product);
        $stockIds = $product->stocks->pluck('id');

        $transactions = $transactions->withCasts(['transactionable_type' => TransactionableTypeName::class])
            ->get()
            ->loadDeepRelations(['order', 'invoice', 'inventory']);

        Log::debug($transactions);

        $transactions = $this->getCalculatedStocks($transactions, $stockIds);

        foreach ($transactions as $transaction) {
            if ($transaction->transactionable_type === 'Исходный остаток') {
                $transaction->user_name = 'Склад';
            }

            if ($transaction->type === 'Out') {
                $transaction->amount = "-" . $transaction->amount;
            }
        }

        return Excel::download(new ProductTransactionsExport($transactions), 'ProductTransactions.xlsx');
    }

    public function bulkSearch(Request $request)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'product_read')->count()) {
            return response('', 403);
        }

        $sku_array = $request->get('sku_array');

        $result = [];

        foreach ($sku_array as $sku) {
            $product = Product::strictSkuFilter($sku)->first(['id', 'main_sku', 'name', 'brand_id']);
            $result[$sku] = $product ? $product->toArray() : null;
        }

        return response()->json($result);
    }

    public function search(SearchProductRequest $request)
    {
        $products = Product::select('*')
            ->joinStocks()
            ->joinOrderProducts()
            ->joinInvoiceProducts()
            ->searchFilters($request);

        return ProductIndexResource::collection($products->get());
    }

    /**
     * Корректирует остатки у stocks товара
     * @param App\Models\V1\Product $product
     * @param \Illuminate\Http\Request  $request
     * @param App\Services\TransactionService $transactionService
     *
     * @return App\Http\Resources\V1\Products\ProductResource $product
     */
    public function correct(Product $product, Request $request, TransactionService $transactionService)
    {
        $this->authorize('correct', $product);

        DB::transaction(function () use ($request, $product, $transactionService) {
            foreach ($request->storePositions as $stock) {
                $difference = $stock['real_stock'] - $stock['new_real_stock'];

                if ($difference < 0) {
                    $transactionService->makeIncomingTransaction(
                        $product,
                        $difference * -1,
                        $product->id,
                        $stock['contractor_id'],
                        $stock['price']
                    );
                } elseif ($difference > 0) {
                    $transactionService->makeOutcomingTransaction(
                        $product,
                        $difference,
                        $product->id,
                        $stock['contractor_id'],
                        $stock['price']
                    );
                }
            }
        });

        return $this->show($product->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $totalStocksAmount = $product->stocks->sum('amount');

        if ($totalStocksAmount) {
            throw new HttpException(422, 'Невозможно удалить товар с ненулевым остатком');
        }

        DB::transaction(function () use (&$product) {
            try {
                $product->stocks()->delete();
                $product->delete();
            } catch (\Exception $e) {
                throw new HttpException(422, 'Невозможно удалить товар, который связан с заказом или счётом');
            }
        });
    }
}
