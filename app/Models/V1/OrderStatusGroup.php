<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbolic_code',
        'name',
    ];

    public function statuses()
    {
        return $this->hasMany(OrderStatus::class, 'status_group_id');
    }
}
