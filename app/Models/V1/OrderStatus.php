<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbolic_code',
        'name',
        'status_group_id',
    ];

    public function statusGroup()
    {
        return $this->belongsTo(OrderStatusGroup::class, 'status_group_id');
    }
}
