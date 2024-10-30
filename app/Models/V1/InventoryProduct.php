<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'inventory_id',
        'product_id',
        'brand_id',
        'brand_name',
        'revision_stock',
        'original_stock',
        'max_price',
        'created_at',
        'updated_at'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Связь transactionable.
     */
    public function transactions()
    {
        return $this->morphMany(Transactionable::class, 'transactionable');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
