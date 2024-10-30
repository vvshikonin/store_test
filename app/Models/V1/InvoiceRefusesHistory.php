<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceRefusesHistory extends Model
{
    use HasFactory;

    protected $table = 'invoice_refuses_history';

    protected $fillable = [
        'invoice_product_id',
        'user_id',
        'amount'
    ];

    public function invoiceProduct()
    {
        return $this->belongsTo(InvoiceProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
