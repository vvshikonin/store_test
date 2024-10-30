<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_payment_responsible',
    ];

    public function scopeIsPaymentResponsible($query)
    {
        $query->where('is_payment_responsible', true);
    }
}
