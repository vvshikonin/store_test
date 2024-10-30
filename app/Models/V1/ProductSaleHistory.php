<?php

namespace App\Models\V1;

use App\Models\V1\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSaleHistory extends Model
{
    use HasFactory;

    protected $table = 'product_sale_history';

    protected $guarded = [
        'id',
        'product_id'
    ];

    protected $fillable = [
        'sale_price',
        'created_at',
        'updated_at',
        'end_date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
