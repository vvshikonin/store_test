<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'contractor_id',
        'price',
        'is_profitable_purchase',
        'last_saled_date',
        'last_receive_date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transactionable::class);
    }

    public function getInvoiceProductsAttribute()
    {
        $invoiceProducts = InvoiceProduct::where('product_id', $this->product_id)
            ->where('price', $this->price)
            ->where('invoices.contractor_id', $this->contractor_id)
            ->join('invoices', 'invoices.id', '=', 'invoice_products.invoice_id')
            ->get();

        return $invoiceProducts;
    }

    public static function aggregateForProduct()
    {
        $aggregated = self::select('stocks.product_id')
            ->selectRaw('SUM(stocks.amount) AS amount')
            ->selectRaw('(SUM(stocks.price * stocks.amount) / SUM(stocks.amount)) AS avg_price')
            ->selectRaw('SUM(stocks.saled) AS saled')
            ->groupBy('stocks.product_id');

        return $aggregated;
    }

    public function getExpectedAttribute()
    {
        $invoiceProducts = $this->invoiceProducts;

        $expected = $invoiceProducts->sum('amount');
        $expected -= $invoiceProducts->sum('received');
        $expected -= $invoiceProducts->sum('refused');
        
        return $expected;
    }
}
