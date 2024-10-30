<?php

namespace App\Models;

use App\Models\V1\MoneyRefundable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyRefundIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method_id',
        'sum',
        'date',
        'is_for_expense'
    ];

    public function moneyRefund()
    {
        return $this->belongsTo(MoneyRefundable::class, 'money_refundable_id');
    }
}
