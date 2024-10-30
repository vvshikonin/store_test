<?php

namespace App\Models\V1;

use App\Support\Collections\TransactionCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Transactionable extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    /**
     * Связь belongsTo Stock.
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Связь belongsTo user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning transactionable model.
     */
    public function transactionable()
    {
        return $this->morphTo();
    }

    /**
     * Получение связанных с транзакцией моделей
     *
     * @param array $relations Массив, содержащий список необходимых для получения моделей
     */
    public function loadDeepRelations(array $relations = [])
    {
        if ($this->transactionable instanceof OrderProduct && in_array('order', $relations)) {
            $this->transactionable->load('order');
        } elseif ($this->transactionable instanceof InvoiceProduct && in_array('invoice', $relations)) {
            $this->transactionable->load('invoice');
        } elseif ($this->transactionable instanceof InventoryProduct && in_array('inventory', $relations)) {
            $this->transactionable->load('inventory');
        }
    }

    public function newCollection(array $models = [])
    {
        return new TransactionCollection($models);
    }

    /**
     * Создает запрос с агрегированными данными для таблицы `invoice_products`.
     *
     * Агрегированные поля:
     * `received_date` - дата оприходования;
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function aggregateForInvoiceProducts()
    {
        $aggregate = self::select('transactionable_id')
            ->selectRaw('MAX(transactionables.created_at) AS received_date')
            ->where('transactionable_type', InvoiceProduct::class)
            ->groupBy('transactionable_id');

        return $aggregate;
    }

    /**
     * Получение списка брендов, по которым проходили транзакции за последние сутки или несколько суток.
     *
     * @param int $days Количество дней, за которые нужно получить транзакции. По умолчанию 1 день.
     * @return \Illuminate\Support\Collection Коллекция уникальных названий брендов.
     */
    public static function getBrandsWithTransactionsForLastDays($days = 1)
    {
        $brandsQuery = self::whereDate('transactionables.created_at', '>=', now()->subDays($days))
                        ->join('stocks', 'transactionables.stock_id', '=', 'stocks.id')
                        ->join('products', 'stocks.product_id', '=', 'products.id')
                        ->join('brands', 'products.brand_id', '=', 'brands.id')
                        ->whereNotNull('brands.xml_link')
                        ->select(
                            'brands.name as name',
                            'brands.xml_link as xml_link'
                        );
        
        $templatesQuery = self::whereDate('transactionables.created_at', '>=', now()->subDays($days))
                        ->join('stocks', 'transactionables.stock_id', '=', 'stocks.id')
                        ->join('products', 'stocks.product_id', '=', 'products.id')
                        ->join('brands', 'products.brand_id', '=', 'brands.id')
                        ->join('xml_templates', function ($join) {
                            $join->whereJsonContains('xml_templates.brand_ids', \DB::raw('CAST(brands.id AS JSON)'));
                        })
                        ->whereNotNull('xml_templates.xml_link')
                        ->select(
                            'xml_templates.name as name',
                            'xml_templates.xml_link as xml_link'
                        );

        return $brandsQuery->union($templatesQuery)->get();
    }
}
