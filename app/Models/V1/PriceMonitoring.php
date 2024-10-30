<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Product;
use App\Http\Resources\V1\PriceMonitoringResource;

class PriceMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'xpath',
        'product_id',
        'parsed_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeProductFilter($query, $product_data)
    {
        $products_ids = Product::select('id')->productFilter($product_data);
        $query->whereIn('product_id', $products_ids);
    }
}
