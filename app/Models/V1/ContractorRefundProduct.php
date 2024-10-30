<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorRefundProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_refund_id',
        'invoice_product_id',
        'amount'
    ];

    public function refund()
    {
        return $this->belongsTo(ContractorRefund::class, 'contractor_refund_id');
    }

    public function contractorRefundStocks()
    {
        return $this->hasMany(ContractorRefundStock::class);
    }

    public function invoiceProduct()
    {
        return $this->belongsTo(InvoiceProduct::class, 'invoice_product_id');
    }

    public static function aggregateForContractorRefund()
    {
        $aggregate = ContractorRefundProduct::select('contractor_refund_id')
            ->selectRaw('SUM(contractor_refund_products.amount * invoice_products.price) AS refund_sum')
            ->joinInvoiceProduct()
            ->groupBy('contractor_refund_id');

        return $aggregate;
    }

    public function scopeJoinInvoiceProduct($query)
    {
        $query->leftJoin('invoice_products', 'invoice_products.id', '=', 'contractor_refund_products.invoice_product_id');
    }
}
