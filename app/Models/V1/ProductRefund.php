<?php

namespace App\Models\V1;

use App\Traits\BaseFilter;
use App\Traits\StringHandle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserStamps;
use App\Events\ProductRefundUpdated;

class ProductRefund extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BaseFilter;
    use StringHandle;
    use UserStamps;

    protected $fillable = [
        'id',
        'order_id',
        'status',
        'comment',
        'product_location',
        'delivery_date',
        'delivery_address'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'completed_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProducts()
    {
        return $this->hasManyThrough(OrderProduct::class, Order::class, 'id', 'order_id', 'order_id', 'id');
    }

    public function moneyRefundable()
    {
        return $this->morphOne(MoneyRefundable::class, 'refundable');
    }

    /**
     * Запускается при инициализации модели. Устанавливает обработчики событий Eloquent.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            event(new ProductRefundUpdated($model));
        });
    }

    /**
     * Фильтр по товарам в возвратах.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $product Название товара или артикул
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductFilter($query, $product)
    {
        if ($product !== null) {
            $product = strtolower($product);

            $product_ids = Product::select('id')->productFilter($product);
            $position_ids = OrderProduct::select('order_products.order_id')->distinct()
                ->whereIn('order_products.product_id', $product_ids);
            $query->whereIn('order_id', $position_ids);
        }
    }

    /**
     * Фильтр по поставщикам в возвратах.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $contractor ID поставщика
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorFilter($query, $contractor)
    {
        if ($contractor !== null) {
            $order_ids = ProductRefund::select('order_products.order_id')->distinct()
                ->leftJoin('order_products', 'product_refunds.order_id', '=', 'order_products.order_id')
                ->where('order_products.deleted_at', null)
                ->whereIn('contractor_id', $contractor);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    /**
     * Фильтрует по основному или дополнительному поставщику.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool|null $isMainContractor Флаг, указывающий, является ли поставщик основным.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorsTypeFilter($query, $isMainContractor)
    {
        if ($isMainContractor !== null) {
            $query->whereHas('orderProducts', function ($query) use ($isMainContractor) {
                $query->whereHas('contractor', function ($subQuery) use ($isMainContractor) {
                    $subQuery->where('is_main_contractor', $isMainContractor);
                });
            });
        }
    }

    /**
     * Фильтр по статусу возврата.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $status Статус возврата
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatusFilter($query, $status)
    {
        if ($status !== null) {
            $query->where('product_refunds.status', $status);
        }
    }

    /**
     * Фильтр по статусу заказа.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $status Статус возврата
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderStatusFilter($query, $orderStatus)
    {
        if ($orderStatus !== null) {
            return $query->where('orders.order_status_id', $orderStatus);
        }
    }

    /**
     * Фильтр по местоположению товара.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location Где товар
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductLocationFilter($query, $location)
    {
        if ($location !== null) {
            $query->whereLike('product_refunds.product_location', $location);
        }
    }

    /**
     * Фильтр по номеру заказа.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $order_number Номер заказа
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderNumberFilter($query, $order_number)
    {
        if ($order_number !== null) {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('orders', 'orders.id', '=', 'product_refunds.order_id')
                ->whereLike('orders.number', $order_number)
                ->orWhere('orders.external_id', $order_number);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    /**
     * Фильтр по комментарию.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $comment Комментарий
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommentFilter($query, $comment)
    {
        if ($comment !== null) {
            $query->whereLike('product_refunds.comment', $comment);
        }
    }

    /**
     * Фильтр по адресу возврата.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $address Адрес возврата
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAddressFilter($query, $address)
    {
        if ($address !== null) {
            $query->whereLike('product_refunds.delivery_address', $address);
        }
    }

    /**
     * Фильтр по количеству товара.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location Количество
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAmountFilter($query, $amounts)
    {
        if ($amounts !== null) {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('order_products', 'product_refunds.order_id', '=', 'order_products.order_id')
                ->betweenFilter('amount', $amounts);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    /**
     * Фильтр по цене товара.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location Цена
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriceFilter($query, $prices)
    {
        if ($prices !== null) {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('order_products', 'product_refunds.order_id', '=', 'order_products.order_id')
                ->betweenFilter('avg_price', $prices);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    /**
     * Фильтр по дате возврата товара.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location Дата возврата
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeliveryDateFilter($query, $dates)
    {
        if ($dates !== null) {
            $query->betweenDateTimeFilter('product_refunds.delivery_date', $dates);
        }
    }

    /**
     * Фильтр по дате создания.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location Дата создания
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedAtFilter($query, $dates)
    {
        if ($dates !== null) {
            $query->betweenDateTimeFilter('product_refunds.created_at', $dates);
        }
    }

    /**
     * Фильтр по дате завершения.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location Дата завершения
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompletedAtFilter($query, $dates)
    {
        if ($dates !== null) {
            $query->betweenDateTimeFilter('product_refunds.completed_at', $dates);
        }
    }

    /**
     * Присоединяет таблицу orders. Делает выборку полей orders.number и orders.external_id
     * с псевдонимами order_number и order_external_id соответственно.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinOrder($query)
    {
        $query->selectRaw('orders.number as order_number')
            ->selectRaw('orders.external_id as order_external_id')
            ->leftJoin('orders', 'orders.id', '=', 'product_refunds.order_id');
    }

    /**
     * Обрабатывает изменение поля delivery_type. Если новое значение (string)'null', меняет на null.
     *
     * @param mixed $value
     */
    public function setDeliveryDateAttribute($value)
    {
        $this->attributes['delivery_date'] = $this->stringNullHandle($value);
    }

    /**
     * Обрабатывает изменение поля payment_date. Если новое значение (string)'null', меняет на null.
     *
     * @param mixed $value
     */
    public function setDeliveryAddressAttribute($value)
    {
        $this->attributes['delivery_address'] = $this->stringNullHandle($value);
    }

    /**
     * Обрабатывает изменение поля comment. Если новое значение (string)'null', меняет на null.
     *
     * @param mixed $value
     */
    public function setCommentAttribute($value)
    {
        $this->attributes['comment'] = $this->stringNullHandle($value);
    }

    /**
     * Обрабатывает загрузку файла на сервер и сохраняет ссылку в БД.
     *
     * @param mixed $file
     */
    public function storeFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = hash('sha256', $file->get());
        $this->refund_file = $file->storeAs('product_refunds', $fileName . '.' . $extension, 'public');
        $this->save();
    }
}
