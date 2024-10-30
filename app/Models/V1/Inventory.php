<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'is_completed',
        'created_at',
        'updated_at'
    ];

    public function getInventoryProductsAttribute()
    {
        $inventory_products = InventoryProduct::select(
            'inventory_products.id', 
            'inventory_products.brand_id', 
            'inventory_products.product_id',
            'inventory_products.max_price',
            'inventory_products.revision_stock',
            'inventory_products.manual_sku',
            'inventory_products.manual_name',
            'inventory_products.is_manually_added',
            'inventory_products.is_corrected',
            'products.name',
            'products.main_sku AS sku',
            DB::raw('COALESCE(products.brand_id, inventory_products.brand_id) AS brand_id'),
            'brands.name AS brand_name'
        )
        ->leftJoin('products', 'products.id', '=', 'inventory_products.product_id')
        ->leftJoin('brands', 'brands.id', '=', 'inventory_products.brand_id');

        if($this->is_completed){
            $inventory_products = $inventory_products->addSelect('inventory_products.original_stock');  
        }

        $inventory_products = $inventory_products->where('inventory_id', $this->id)->orderBy('id', 'desc')->get();
        return $inventory_products;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInventoryProductFilter($query, $product_data)
    {
        $products = Product::select('id')->productFilter($product_data);

        $inventorys_ids = InventoryProduct::select('inventory_id')->whereIn('product_id', $products);
        
        $query->whereIn('id', $inventorys_ids);
    }
    
    public function scopeStatusFilter($query, $status)
    {
        $query->where('inventories.is_completed', $status);
    }

    public function scopeUserFilter($query, $user)
    {
        $query->where('inventories.user_id', $user);
    }

    public function scopeInventoryDateFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null && $end !== null) {
            $query->whereBetween('inventories.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        } else if ($start !== null) {
            $query->where('inventories.created_at', '>=', $start . ' 00:00:00');
        } else if ($start === null && $end !== null && $equal === null && $not_equal === null) {
            $query->where('inventories.created_at', '<=', $end . ' 23:59:59');
        } else if ($equal !== null) {
            $query->whereBetween('inventories.created_at', [$equal . ' 00:00:00', $equal . ' 23:59:59']);
        } else if ($not_equal !== null) {
            $query->whereNotBetween('inventories.created_at', [$not_equal . ' 00:00:00', $not_equal . ' 23:59:59']);
        }
    }
}
