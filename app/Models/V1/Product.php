<?php

namespace App\Models\V1;

use App\Models\V1\Invoice;
use App\Models\V1\InvoicePosition;
use App\Models\V1\ProductSaleHistory;
use App\Events\ProductUpdated;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BaseFilter;
use App\Traits\UserStamps;
use App\Filters\ProductFilters;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BaseFilter;
    use UserStamps;

    protected $fillable = [
        'main_sku',
        'brand_id',
        'name',
        'maintained_balance',
        'user_comment',
        'rrp',
        'is_sale',
        'sale_type',
        'sale_multiplier',
        'sale_percent',
        'sale_fixed_price',
        'strikethrough_multiplier'
    ];

    protected $guarded = [
        'id',
        'sku_list'
    ];

    protected $filters = ProductFilters::class;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            event(new ProductUpdated($model));
        });
    }
    /**
     * Связь transactionable.
     */
    public function transactions()
    {
        return $this->morphMany(Transactionable::class, 'transactionable');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    // public function orderPositions()
    // {
    //     return $this->hasMany(OrderPosition::class);
    // }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // public function invoicePositions()
    // {
    //     return $this->hasManyThrough(InvoicePosition::class, Stock::class);
    // }

    public function invoiceProducts()
    {
        return $this->hasMany(InvoiceProduct::class);
    }
    
    public function saleHistory()
    {
        return $this->hasMany(ProductSaleHistory::class);
    }

    public function getContractorIdsAttribute($hasStock = false)
    {
        $query = Contractor::select('contractors.id')
            ->join('stocks', 'stocks.contractor_id', '=', 'contractors.id')
            ->where('stocks.product_id', $this->id);

        if ($hasStock) {
            $query->where('stocks.amount', '>', 0);
        }

        return $query->pluck('id');
    }

    public function getContractorNamesAttribute()
    {
        return Contractor::select('contractors.name')->distinct()
            ->join('stocks', 'stocks.contractor_id', '=', 'contractors.id')
            ->where('stocks.product_id', $this->id)
            ->pluck('name');
    }

    /**
     * Считает средневзвешенную цену товара по его остаткам
     *
     * @return float
     */
    public function getAveragePriceAttribute()
    {
        $totalPrice = 0;
        $totalAmount = 0;

        foreach ($this->stocks as $stock) {
            $totalPrice += $stock->price * $stock->amount;
            $totalAmount += $stock->amount;
        }

        if ($totalAmount == 0) {
            return 0;
        }

        return $totalPrice / $totalAmount;
    }

    /**
     * Реализует формулы подсчета цены продажи в зависимости от условий
     *
     * @return float возвращает подсчитанную цену продажи после учета маржи
     */
    public function getSalePriceAttribute()
    {
        $logableSKU = "JBLCLIP4BLK";

        $contractorIds = $this->getContractorIdsAttribute(true);
        $averagePrice = $this->averagePrice;

        if ($this->main_sku == $logableSKU)
            Log::debug('Устанавливаем цену для JBLCLIP4BLK');

        $isAdditionalContractor = 0;
        $contractorMarginality = 0;

        foreach ($contractorIds as $id) {
            $contractor = Contractor::find($id);

            if (!$contractor->is_main_contractor) {
                if ($this->main_sku == $logableSKU)
                    Log::debug('Найден доп. поставщик');

                $isAdditionalContractor = 1;
                $contractorMarginality = $contractor->marginality;

                break;
            }
        }

        if (!$this->maintained_balance) {
            if ($this->main_sku == $logableSKU)
                Log::debug('Нет поддерживаемого остатка');

            return $averagePrice / (1 - $this->brand->marginality / 100);
        } else {
            if ($this->main_sku == $logableSKU)
                Log::debug('Есть поддерживаемый остаток');

            if ($averagePrice > 70000) {
                if ($this->main_sku == $logableSKU)
                    Log::debug('Средняя цена больше 70000');

                return $averagePrice / 0.8;
            } elseif (!$isAdditionalContractor) {
                if ($this->main_sku == $logableSKU)
                    Log::debug('Основной поставщик');

                return $averagePrice / (1 - $this->brand->maintained_marginality / 100);
            } else {
                if ($this->main_sku == $logableSKU)
                    Log::debug('Доп поставщик без под. остатка');

                return $averagePrice / (1 - $contractorMarginality / 100);
            }
        }
    }

    public function getProductOrdersAttribute()
    {
        $orders = OrderProduct::select('order_id')
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw('MAX(orders.number) AS number')
            ->selectRaw('MAX(orders.external_id) AS crm_id')
            ->selectRaw('MAX(orders.state) AS state')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('product_id', $this->id)
            ->where('orders.state', 'reserved')
            ->groupBy('order_id')
            ->get();
        return $orders;
    }

    public function getProductWithTrashedOrdersAttribute()
    {
        $orders = OrderProduct::select('order_id', 'orders.deleted_at')
            ->selectRaw('amount AS amount')
            ->selectRaw('contractors.name as contractor_name')
            ->selectRaw('orders.number AS number')
            ->selectRaw('orders.external_id AS crm_id')
            ->selectRaw('orders.state AS state')
            ->selectRaw('order_statuses.name AS order_status')
            ->selectRaw('order_statuses.status_group_id AS order_group')
            ->selectRaw('order_status_groups.symbolic_code AS group_code')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('order_statuses', 'order_statuses.id', '=', 'orders.order_status_id')
            ->join('order_status_groups', 'order_statuses.status_group_id', '=', 'order_status_groups.id')
            ->join('contractors', 'contractors.id', '=', 'order_products.contractor_id')
            ->where('product_id', $this->id)
            // ->where('orders.state', 'reserved')
            // ->groupBy('order_id')
            ->get();
        return $orders;
    }

    public function getProductInvoicesAttribute()
    {
        $invoices = InvoiceProduct::select(
            'invoices.id',
            'invoice_products.amount',
            'invoice_products.price',
            'number',
            'invoices.contractor_id',
            'contractors.name as contractor_name',
            'invoices.created_at',
            'invoice_products.product_id',
            'status',
            'received',
            'refused'
        )
            ->join('invoices', 'invoices.id', '=', 'invoice_products.invoice_id')
            ->join('contractors', 'invoices.contractor_id', '=', 'contractors.id')
            // ->leftJoin('stocks', 'invoice_products.product_id', '=', 'stocks.product_id')
            ->where('invoice_products.product_id', $this->id)
            ->get();

        return $invoices;
    }

    public function scopeProductFilter($query, $product_data)
    {
        $query->skuFilter($product_data)
            ->orWhere('products.name', 'LIKE', '%' . $product_data . '%');
        // $query->whereJsonContains('products.sku_list', $product_data)->orWhere('products.name', 'LIKE', '%' . $product_data . '%');
    }

    /**
     * Фильтр по общему количеству остатков всех связанных stocks у продукта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $start Начало диапазона для фильтрации.
     * @param string|null $end Конец диапазона для фильтрации.
     * @param string|null $equal Значение для точного соответствия.
     * @param string|null $notEqual Значение для несоответствия.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStockCountFilter($query, $start, $end, $equal, $notEqual)
    {
        // Подзапрос для агрегации количества по каждому продукту
        $stockAmounts = Stock::selectRaw('product_id, SUM(amount) as total_amount')
            ->groupBy('product_id');

        // Присоединяем подзапрос к основному запросу продуктов
        $query->joinSub($stockAmounts, 'stock_amounts', function ($join) {
            $join->on('products.id', '=', 'stock_amounts.product_id');
        });

        // Применяем условия фильтрации
        if ($start !== null && $end !== null) {
            $query->whereBetween('stock_amounts.total_amount', [$start, $end]);
        } elseif ($start !== null) {
            $query->where('stock_amounts.total_amount', '>=', $start);
        } elseif ($end !== null) {
            $query->where('stock_amounts.total_amount', '<=', $end);
        } elseif ($equal !== null) {
            $query->where('stock_amounts.total_amount', $equal);
        } elseif ($notEqual !== null) {
            $query->where('stock_amounts.total_amount', '!=', $notEqual);
        }
    }

    public function scopeBrandFilter($query, $brand_ids)
    {
        if ($brand_ids !== null) {
            $query->whereIn('products.brand_id', $brand_ids);
        }
    }

    public function scopeSkuFilter($query, $product_data)
    {
        $product_data = mb_strtolower($product_data, 'UTF-8');
        $query->where(function ($query) use ($product_data) {
            $query->whereRaw("LOWER(JSON_EXTRACT(sku_list, '$[*]')) LIKE ?", ["%{$product_data}%"]);
        });
    }

    public function scopeStrictSkuFilter($query, $product_data)
    {
        $product_data = mb_strtolower($product_data, 'UTF-8');
        $query->whereRaw("JSON_SEARCH(LOWER(CAST(sku_list AS CHAR)), 'one', ?) IS NOT NULL", ["{$product_data}"]);
    }

    public function scopeCommentFilter($query, $comment)
    {
        if ($comment !== null) {
            $store_positions = Stock::where('stocks.user_comment', 'LIKE', '%' . $comment . '%')->select('product_id')->distinct();
            $query->whereIn('products.id', $store_positions);
        }
    }

    public function scopeOrderFilter($query, $order_data)
    {
        if ($order_data !== null) {
            $ordersSub = Order::select('order_products.product_id')
                ->join('order_products', 'order_products.order_id', '=', 'orders.id')
                ->where('order_products.deleted_at', null)
                ->where('orders.number', 'LIKE', '%' . $order_data . '%')
                ->where('orders.state', 'reserved');
            // $order_positions = OrderProduct::select('product_id')->distinct()
            //     ->join('orders', 'orders.id', '=', 'order_products.order_id')
            //     ->where(function ($query) use ($order_data){
            //         $query->where('orders.number', 'LIKE', '%' . $order_data . '%')
            //             ->where('orders.state', 'reserved');
            //     })
            //     ->orWhere(function ($query) use ($order_data){
            //         $query->where('orders.external_id', $order_data)
            //             ->where('orders.state', 'reserved');
            //     });
            $query->whereIn('products.id', $ordersSub);
        }
    }

    public function scopeContractorsFilter($query, $contractors_ids)
    {
        if ($contractors_ids !== null) {
            // $store_positions = StorePosition::whereIn('store_positions.contractor_id', [$contractors])->select('product_id')->distinct();
            // $query->whereIn('products.id', $store_positions);

            $query->whereHas('stocks', function ($query) use ($contractors_ids) {
                $query->whereIn('contractor_id', $contractors_ids);
            });
        }
    }

    public function scopeProductPriceFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null && $end !== null) {
            $store_positions = Stock::whereBetween('stocks.price', [$start, $end])->select('product_id')->distinct();
            $query->whereIn('products.id', $store_positions);
        } else if ($start !== null) {
            $store_positions = Stock::where('stocks.price', '>=', $start)->select('product_id')->distinct();
            $query->whereIn('products.id', $store_positions);
        } else if ($start === null && $end !== null && $equal === null && $not_equal === null) {
            $store_positions = Stock::where('stocks.price', '<=', $end)->select('product_id')->distinct();
            $query->whereIn('products.id', $store_positions);
        } else if ($equal !== null) {
            $store_positions = Stock::where('stocks.price', $equal)->select('product_id')->distinct();
            $query->whereIn('products.id', $store_positions);
        } else if ($not_equal !== null) {
            $store_positions = Stock::where('stocks.price', '!=', $not_equal)->select('product_id')->distinct();
            $query->whereIn('products.id', $store_positions);
        }
    }

    public function scopeProductAvgPriceFilter($query, $start, $end, $equal, $notEqual)
    {
        // Подзапрос для расчета средне-взвешенной цены каждого продукта
        // Используем CAST для определения точности вычислений (например, до двух знаков после запятой)
        $avgPrices = Stock::selectRaw('product_id, CAST(SUM(price * amount) / SUM(amount) AS DECIMAL(10, 2)) as weighted_avg_price')
            ->where('amount', '>', 0)
            ->groupBy('product_id');

        // Присоединяем подзапрос
        $query->joinSub($avgPrices, 'avg_prices', function ($join) {
            $join->on('products.id', '=', 'avg_prices.product_id');
        });

        // Применяем фильтры
        if ($start !== null && $end !== null) {
            $query->whereBetween('avg_prices.weighted_avg_price', [$start, $end]);
        } elseif ($start !== null) {
            $query->where('avg_prices.weighted_avg_price', '>=', $start);
        } elseif ($end !== null) {
            $query->where('avg_prices.weighted_avg_price', '<=', $end);
        } elseif ($equal !== null) {
            $query->where('avg_prices.weighted_avg_price', '=', $equal);
        } elseif ($notEqual !== null) {
            $query->where('avg_prices.weighted_avg_price', '!=', $notEqual);
        }
    }

    public function scopeProductUpdateDateFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null && $end !== null) {
            $query->whereBetween('products.updated_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        } else if ($start !== null) {
            $query->where('products.updated_at', '>=', $start . ' 00:00:00');
        } else if ($start === null && $end !== null && $equal === null && $not_equal === null) {
            $query->where('products.updated_at', '<=', $end . ' 23:59:59');
        } else if ($equal !== null) {
            $query->whereBetween('products.updated_at', [$equal . ' 00:00:00', $equal . ' 23:59:59']);
        } else if ($not_equal !== null) {
            $query->whereNotBetween('products.updated_at', [$not_equal . ' 00:00:00', $not_equal . ' 23:59:59']);
        }
    }

    public function scopeProductDeliveryDateFilter($query, $start, $end, $equal, $not_equal)
    {
        $invoice_ids = null;

        if ($start !== null && $end !== null) {

            $invoice_ids = Invoice::select('id')->whereBetween('invoices.planed_delivery_date', [$start, $end]);
        } else if ($start !== null) {

            $invoice_ids = Invoice::select('id')->where('invoices.planed_delivery_date', '>=', $start);
        } else if ($start === null && $end !== null && $equal === null && $not_equal === null) {

            $invoice_ids = Invoice::select('id')->where('invoices.planed_delivery_date', '<=', $end);
        } else if ($equal !== null) {

            $invoice_ids = Invoice::select('id')->where('invoices.planed_delivery_date', '=', $equal);
        } else if ($not_equal !== null) {

            $invoice_ids = Invoice::select('id')->where('invoices.planed_delivery_date', '!=', $not_equal);
        }

        if ($invoice_ids !== null) {

            $store_positions_sub = InvoiceProduct::select('invoice_products.product_id')->whereIn('invoice_id', $invoice_ids);

            $product_ids = Stock::select('stock.product_id')->whereIn('stock.id', $store_positions_sub);

            $query->whereIn('products.id', $product_ids);
        }
    }

    public function scopeHasSale($query)
    {
        $query->havingRaw('is_sale = 1');
    }

    public function scopeHasExpected($query)
    {
        $query->whereRaw('COALESCE(invoice_products.expected, 0) > 0');
    }

    public function scopeHasWarning($query)
    {
        $query->whereRaw('(stocks.amount + COALESCE(invoice_products.expected, 0) < COALESCE(order_products.reserved, 0 )
            OR stocks.amount + COALESCE(invoice_products.expected, 0) < COALESCE(products.maintained_balance, 0))');
    }

    public function scopeIsProfitablePurchase($query)
    {
        $query->whereHas('stocks', function ($query) {
            $query->where('is_profitable_purchase', true);
        });
    }

    public function scopeMaintainedBalanceState($query, $state)
    {
        Log::debug("state: $state");
        if ($state == 1) {
            $query->where('maintained_balance', '>', 0);
        } elseif ($state == 2) {
            $query->where(function ($query) {
                $query->where('maintained_balance', '<=', 0)
                    ->orWhereNull('maintained_balance');
            });
        }
        // Нет действия для $state == 0, так как это состояние "без фильтра".
    }

    public function scopeHasOrders($query)
    {
        $query->whereRaw('COALESCE(reserved, 0) > 0');
    }

    public function scopeHasRealStock($query)
    {
        $query->whereRaw('COALESCE(stocks.amount, 0) > 0');
    }

    public function calculateReservedAmount()
    {
        return OrderProduct::join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('order_products.product_id', $this->id)
            ->where('orders.state', 'reserved')
            ->sum('order_products.amount');
    }

    public function scopeHasFreeStock($query)
    {
        $query->whereRaw('COALESCE(stocks.amount, 0) - (SELECT COALESCE(SUM(order_products.amount), 0) 
                          FROM order_products 
                          JOIN orders ON order_products.order_id = orders.id 
                          WHERE order_products.product_id = products.id AND orders.state = ?) > 0', ['reserved']);
    }

    // public function scopeHasFreeStock($query)
    // {
    //     $query->whereRaw('COALESCE(stocks.amount, 0) - COALESCE(order_products.reserved, 0) > 0');
    // }

    public function scopePaymentStatus($query, $status)
    {
        return $query->whereHas('invoiceProducts.invoice', function ($query) use ($status) {
            switch ($status) {
                case 0:
                    $query->where('payment_status', 0)->where('payment_confirm', 0);
                    break;
                case 1:
                    $query->where('payment_status', 1)->where('payment_confirm', 0);
                    break;
                case 2:
                    $query->where('payment_status', 1)->where('payment_confirm', 1);
                    break;
            }
        });
    }

    public function scopeJoinOrderProducts($query)
    {
        $OrderProductsSub = OrderProduct::aggregateForProduct();
        $query->leftJoinSub($OrderProductsSub, 'order_products', function ($join) {
            $join->on('products.id', '=', 'order_products.product_id');
        });
    }

    public function scopeJoinStocks($query)
    {
        $stocksSub = Stock::aggregateForProduct();
        $query->leftJoinSub($stocksSub, 'stocks', function ($join) {
            $join->on('products.id', '=', 'stocks.product_id');
        });
    }

    public function scopeJoinInvoiceProducts($query)
    {
        $invocieProductsSub = InvoiceProduct::aggregateForProduct();
        $query->leftJoinSub($invocieProductsSub, 'invoice_products', function ($join) {
            $join->on('products.id', '=', 'invoice_products.product_id');
        });
    }

    public function scopeSearchFilters($query, $request)
    {
        // dd($request->searchStringProduct);
        if ($request->searchStringProduct)
            $query->productFilter($request->searchStringProduct);
        else if ($request->searchStringOrder)
            $query->orderFilter($request->searchStringOrder);

        if ($request->boolean('hasRealStok'))
            $query->hasRealStock();

        if ($request->boolean('hasFreeStock'))
            $query->hasFreeStock();

        if ($request->boolean('hasMaintainedBalance'))
            $query->hasMainteinedBalance();

        if ($request->boolean('hasExpected'))
            $query->hasExpected();

        if ($request->boolean('hasReserve'))
            $query->hasOrders();
    }
}
