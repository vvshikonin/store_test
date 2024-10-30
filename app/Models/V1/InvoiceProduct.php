<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BaseFilter;
use App\Traits\StringHandle;

/** 
 * @property integer $amount
 * @property integer $invoice_id 
 * @property integer $product_id
 * @property float $price
 * @property string|null $planned_delivery_date_from
 * @property string|null $planned_delivery_date_to
 * @property integer $received
 * @property string|null $received_at
 * @property integer $refused
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceProduct query()
*/

class InvoiceProduct extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BaseFilter;
    use StringHandle;

    protected $fillable = [
        'amount',
        'invoice_id',
        'product_id',
        'price',
        'planned_delivery_date_from',
        'planned_delivery_date_to',
        'delivery_type'
    ];

    protected $guarded = [
        'received',
        'received_at',
        'refused',
    ];

    protected $touches = ['invoice'];

    /**
     * Связь belongsTo Product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Связь belongsTo Invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Связи hasMany со stocks
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id', 'product_id');
    }

    /**
     * Связь transactionable.
     */
    public function transactions()
    {
        return $this->morphMany(Transactionable::class, 'transactionable');
    }

    /**
     * Связь hasMany c InvoiceRefusesHistory.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refusesHistory()
    {
        return $this->hasMany(InvoiceRefusesHistory::class);
    }

    /**
     * Присоединяет таблицу `transactionables` с агрегированными данными.
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinTransactions($query)
    {
        $transactions = Transactionable::aggregateForInvoiceProducts();
        $query->leftJoinSub($transactions, 'transactionables', function ($join) {
            $join->on('transactionables.transactionable_id', '=', 'invoice_products.id');
        });
    }

    /**
     * Присоединяет таблицу `invoices` и связанные с ней таблицы:
     * `contractors`,
     * `legal_entities`,
     * `payment_methods`.
     *
     * Присоединяет поля:
     * `invoice_number` - номер счёта;
     * `invoice_date` - дата счёта;
     * `invoice_status` - статус счёта;
     * `invoice_delivery_type` - способ доставки;
     * `invoice_comment` - комментарий счёта;
     * `invoice_payment_status` - статус оплаты счёта;
     * `invoice_payment_confirm` - статус подтверждения оплаты счёта;
     * `invoice_payment_date` - дата оплаты счёта;
     * `contractor_name` - имя поставщика;
     * `legal_entity_name` - имя юр. лица;
     * `payment_methods` - название способа оплаты;
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinInvoice($query)
    {
        $query->selectRaw('invoices.number AS invoice_number')
            ->selectRaw('invoices.status AS invoice_status')
            ->selectRaw('invoices.status_set_at AS invoice_status_set_at')
            ->selectRaw('invoices.date AS invoice_date')
            ->selectRaw('invoices.delivery_type AS invoice_delivery_type')
            ->selectRaw('invoices.comment AS invoice_comment')
            ->selectRaw('invoices.payment_status AS invoice_payment_status')
            ->selectRaw('invoices.payment_confirm AS invoice_payment_confirm')
            ->selectRaw('invoices.payment_date AS invoice_payment_date')
            ->selectRaw('contractors.name AS contractor_name')
            ->selectRaw('legal_entities.name AS legal_entity_name')
            ->selectRaw('payment_methods.name AS payment_method_name')
            ->leftJoin('invoices', 'invoices.id', '=', 'invoice_products.invoice_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'invoices.contractor_id')
            ->leftJoin('legal_entities', 'legal_entities.id', '=', 'invoices.legal_entity_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'invoices.payment_method_id');
    }

    /**
     * Присоединяет таблицу `products` и поля `product_name` - название товара, `product_sku` - основной артикул товара.
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinProduct($query)
    {
        $query->selectRaw('products.name AS product_name')
            ->selectRaw('products.main_sku AS product_sku')
            ->selectRaw('brands.name AS product_brand')
            ->leftJoin('products', 'products.id', '=', 'invoice_products.product_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id');
    }

    /**
     * Создает запрос с агрегированными данными для таблицы `product`.
     *
     * Агрегированные поля:
     * `expected` - количество ожидаемого товара.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function aggregateForProduct()
    {
        $aggregated = InvoiceProduct::select('product_id')
            ->selectRaw('SUM(amount - received - refused) AS expected')
            ->groupBy('product_id');

        return $aggregated;
    }

    /**
     * Создает запрос с агрегированными данными для таблицы `invoice`.
     *
     * Агрегированные поля:
     * `total_sum` - сумма счёта;
     * `received_sum` - сумма оприходованного;
     * `refused_sum` - сумма отказа;
     * `expected_sum` - сумма ожидаемого;
     * `planned_delivery_date` - дата доставки;
     * `min_delivery_date` - минимальная дата доставки;
     * `max_delivery_date` - максимальная дата доставки;
     * `received_date` - дата оприходования;
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function aggregateForInvoice()
    {
        $aggregate = InvoiceProduct::select('invoice_id')
            ->selectRaw('SUM(invoice_products.amount * invoice_products.price) AS total_sum')
            ->selectRaw('SUM(invoice_products.received * invoice_products.price) AS received_sum')
            ->selectRaw('SUM(invoice_products.refused * invoice_products.price) AS refused_sum')
            ->selectRaw('SUM((invoice_products.amount - invoice_products.received - invoice_products.refused) * invoice_products.price) AS expected_sum')
            ->selectRaw('MIN(planned_delivery_date_from) as planned_delivery_date')
            ->selectRaw('MIN(planned_delivery_date_from) as min_delivery_date')
            ->selectRaw('MAX(planned_delivery_date_to) as max_delivery_date')
            ->selectRaw('MAX(invoice_products.received_at) as received_at')
            ->groupBy('invoice_id');

        return $aggregate;
    }

    /**
     * Производит фильтрацию по полям `planned_delivery_date_from` и `planned_delivery_date_to` на основе переданного объекта `$dates`.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates `json` строка с ключами: `start`, `end`, `equal`, `notEqual`.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlannedDeliveryDateFilter($query, $dates)
    {
        $dates = json_decode($dates);

        $start = property_exists($dates, 'start') ? $dates->start : null;
        $end = property_exists($dates, 'end') ? $dates->end : null;
        $equal = property_exists($dates, 'equal') ? $dates->equal : null;
        $notEqual = property_exists($dates, 'notEqual') ? $dates->notEqual : null;

        $makeFilterClosure = function ($date) {
            return function ($query) use ($date) {
                $query
                    ->where(function ($query) use ($date) {
                        $query
                            ->where('planned_delivery_date_from', '<', $date)
                            ->where('planned_delivery_date_to', '>', $date);
                    })
                    ->orWhere('planned_delivery_date_from', $date)
                    ->orWhere('planned_delivery_date_to', $date);
            };
        };

        if ($start != null && $end != null) {
            $query->where($makeFilterClosure($start))->orWhere($makeFilterClosure($end));
        } else if ($start != null) {
            $query->where($makeFilterClosure($start));
        } else if ($end != null) {
            $query->where($makeFilterClosure($end));
        } else if ($equal != null) {
            $query->where($makeFilterClosure($equal));
        } else if ($notEqual != null) {
            $query
                ->where(function ($query) use ($notEqual) {
                    $query
                        ->where('planned_delivery_date_from', '>', $notEqual)
                        ->orWhere('planned_delivery_date_to', '<', $notEqual);
                })
                ->where('planned_delivery_date_from', '!=', $notEqual)
                ->where('planned_delivery_date_to', '!=', $notEqual);
        }
    }

    /**
     * Добавляет в выборку поле `invoice_sum` получаемое из подзапроса.
     */
    public function scopeAddInvoiceSum($query){
        $query->addSelect([
            'invoice_sum' => InvoiceProduct::query()
                ->from('invoice_products as sub')
                ->selectRaw('SUM(amount * price) as invoice_sum')
                ->whereColumn('invoice_products.invoice_id', 'sub.invoice_id')
                ->groupBy('invoice_id')
        ]);
    }

    /**
     * Добавляет в выборку поле `invoice_received_sum` получаемое из подзапроса.
     */
    public function scopeAddInvoiceReceivedSum($query){
        $query->addSelect([
            'invoice_received_sum' => InvoiceProduct::query()
                ->from('invoice_products as sub')
                ->selectRaw('SUM(price * received) as invoice_received_sum')
                ->whereColumn('invoice_products.invoice_id', 'sub.invoice_id')
                ->groupBy('invoice_id')
        ]);
    }
}
