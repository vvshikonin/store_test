<?php

namespace App\Models\V1;

use App\Traits\BaseFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UserStamps;

class ContractorRefund extends Model
{
    use HasFactory;
    use BaseFilter;
    use UserStamps;

    protected $fillable = [
        'invoice_id',
        'comment',
        'delivery_date',
        'delivery_address',
        'delivery_status',
        'updated_by',
        'created_by'
    ];

    protected $guarded = [
        'is_complete'
    ];

    public function contractorRefundProducts()
    {
        return $this->hasMany(ContractorRefundProduct::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function moneyRefundable()
    {
        return $this->morphOne(MoneyRefundable::class, 'refundable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // public function contractor()
    // {
    //     return $this->hasOneThrough(
    //         Contractor::class,
    //         Invoice::class,
    //         'contractor_id', // Внешний ключ на конечной модели (Contractor) в промежуточной модели (Invoice)
    //         'id', // Локальный ключ на конечной модели (Contractor)
    //         'invoice_id', // Локальный ключ на начальной модели (ContractorRefund)
    //         'id'  // Локальный ключ на промежуточной модели (Invoice)
    //     );
    // }

    public function scopeContractorRefundSum($query)
    {
        $contractorRefundProductSub = ContractorRefundProduct::aggregateForContractorRefund();

        $query->leftJoinSub($contractorRefundProductSub, 'contractorRefundProducts', function ($join) {
            $join->on('contractorRefundProducts.contractor_refund_id', '=', 'contractor_refunds.id');
        });
    }

    public function saveContractorRefundFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = hash('sha256', $file->get());
        $this->refund_documents = $file->storeAs('contractor_refunds', $fileName . '.' . $extension, 'public');
        $this->save();
    }

    /**
     * Фильтр по номеру счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $invoiceNumber Номер счёта для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInvoiceNumberFilter($query, $invoiceNumber)
    {
        if ($invoiceNumber !== null) {
            $invoiceIds = Invoice::select('id')->whereLike('number', $invoiceNumber);
            $query->whereIn('invoice_id', $invoiceIds);
        }
    }

    /**
     * Фильтр по поставщику.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $contractorsIDs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorFilter($query, $contractorsIDs)
    {
        if ($contractorsIDs !== null) {
            $invoicesIDs = Invoice::select('id')->whereIn('contractor_id', $contractorsIDs);
            $query->whereIn('invoice_id', $invoicesIDs);
        }
    }

    /**
     * Фильтрует по основному или дополнительному поставщику.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool|null $isMainContractor Флаг, указывающий, является ли поставщик основным.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorsTypeFilter($query, $isMainContractor)
    {
        if ($isMainContractor !== null) {
            $query->whereHas('invoice', function ($query) use ($isMainContractor) {
                $query->whereHas('contractor', function ($subQuery) use ($isMainContractor) {
                    $subQuery->where('is_main_contractor', $isMainContractor);
                });
            });
        }
    }

    /**
     * Фильтр по товару.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $contractorsIDs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductFilter($query, $product)
    {
        if ($product !== null) {
            $productsIDs = Product::select('id')->productFilter($product);
            $invoiceProductsIDs = InvoiceProduct::select('id')->whereIn('product_id', $productsIDs);
            $contractorRefundsIDs = ContractorRefundProduct::select('contractor_refund_id')->whereIn('invoice_product_id', $invoiceProductsIDs);
            $query->whereIn('contractor_refunds.id', $contractorRefundsIDs);
        }
    }


    /**
     * Фильтр по комментарию.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $comment Комментарий счёта для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommentFilter($query, $comment)
    {
        if ($comment !== null) {
            $query->whereLike('comment', $comment);
        }
    }

    /**
     * Фильтр по адресу.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $deliveryAddress Комментарий счёта для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeliveryAddressFilter($query, $deliveryAddress)
    {
        if ($deliveryAddress !== null) {
            $query->whereLike('delivery_address', $deliveryAddress);
        }
    }

    /**
     * Фильтр по статусу возврата.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $completeStatuses id поставщиков.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsCompleteFilter($query, $completeStatuses)
    {
        if ($completeStatuses !== null) {
            $query->whereIn('is_complete', $completeStatuses);
        }
    }

    /**
     * Фильтр по статусу доставки.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $contractorsIds id поставщиков.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeliveryStatusFilter($query, $deliveryStatuses)
    {
        if ($deliveryStatuses !== null) {
            $query->whereIn('delivery_status', $deliveryStatuses);
        }
    }

    /**
     * Фильтр по сумме.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $sums массив дат оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRefundSumFilter($query, $sums)
    {
        $contractorRefundIDs = ContractorRefund::select('id')
            ->contractorRefundSum()
            ->betweenFilter('contractorRefundProducts.refund_sum', $sums);

        $query->whereIn('id', $contractorRefundIDs);
    }

    /**
     * Фильтр по дате доставки.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $dates массив дат оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeliveryDateFilter($query, $dates)
    {
        $query->betweenFilter('delivery_date', $dates);
    }

    /**
     * Присоединяет таблицу `invoices`.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinInvoices($query)
    {
        $query->leftJoin('invoices', 'invoices.id', '=', 'invoice_id');
    }

    /**
     * Присоединяет таблицу `contractors`.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinContractors($query)
    {
        $query->leftJoin('contractors', 'contractors.id', '=', 'invoices.contractor_id');
    }

    public function getPaymentMethodIdAttribute()
    {
        return $this->invoice->payment_method_id;
    }

    public function getContractorAttribute()
    {
        return $this->invoice->contractor;
    }
}
