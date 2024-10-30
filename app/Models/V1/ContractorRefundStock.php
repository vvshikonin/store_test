<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorRefundStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_refund_product_id',
        'stock_id',
        'amount'
    ];

    public function product()
    {
        return $this->belongsTo(ContractorRefundProduct::class, 'contractor_refund_product_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }
}
