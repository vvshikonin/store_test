<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sum',
        'invoice_id',
        'money_refundable_id'
    ];

    /**
     * Отношение BelongsTo c MoneyRefundable.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moneyRefundable()
    {
        return $this->belongsTo(MoneyRefundable::class);
    }

    public function getContractorAttribute(){
        return $this->moneyRefundable->contractor;
    }
}
