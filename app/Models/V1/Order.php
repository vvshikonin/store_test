<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BaseFilter;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BaseFilter;

    protected $fillable = [
        'number',
        'order_status_id',
        'external_id',
    ];

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);

    }

    public function defect(){
        return $this->hasOne(Defect::class);
    }

    public function productRefund(){
        return $this->hasOne(ProductRefund::class);
    }

}
