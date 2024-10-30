<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'external_id',
        'amount',
        'avg_price',
        'product_id',
        'contractor_id',
        'amount',
        'avg_price',
        'contractor_id',
    ];

    protected $guarded = [];

    /**
     * Связь transactionable.
     */
    public function transactions()
    {
        return $this->morphMany(Transactionable::class, 'transactionable');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public static function aggregateForProduct()
    {
        $aggregated =  self::select('product_id')
            ->selectRaw('SUM(amount) AS reserved')
            ->joinOrder()
            ->where('orders.state', 'reserved')
            ->groupBy('product_id');

        return $aggregated;
    }

    public function scopeJoinOrder($query)
    {
        $query->join('orders', 'orders.id', '=', 'order_products.order_id');
    }
}
