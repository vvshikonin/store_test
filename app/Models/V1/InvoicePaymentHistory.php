<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'invoice_payment_history';

    protected $fillable = [
        'invoice_id',
        'status',
        'payment_method_id',
        'legal_entity_id',
        'payment_date',
        'user_id',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
