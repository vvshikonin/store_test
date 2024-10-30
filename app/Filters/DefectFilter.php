<?php

namespace App\Filters;

use App\Models\V1\OrderProduct;

trait DefectFilter
{
    use BaseFilter;

    public function scopeApplyFilters($query, $filters)
    {
        return $query->orderFilter($filters->orderFilter)
            ->productFilter($filters->productFilter)
            ->contractorsFilter($filters->contractorsFilter)
            ->contractorsTypeFilter($filters->contractorsTypeFilter)
            ->createdAtFilter(
                $filters->created_at_start,
                $filters->created_at_end,
                $filters->created_at_equal,
                $filters->created_at_not_equal
            )
            ->legalEntityFilter($filters->legal_entity_ids)
            ->paymentMethodFilter($filters->payment_method_ids)
            ->deliveryAddressFilter($filters->deliveryAddressFilter)
            ->productLocationFilter($filters->product_location_ids)
            ->replacementTypeFilter($filters->replacement_type_ids)
            ->moneyRefundStatusFilter($filters->money_refund_status_ids)
            ->refundTypeFilter($filters->refund_type_ids)
            ->deliveryDateFilter(
                $filters->delivery_date_start,
                $filters->delivery_date_end,
                $filters->delivery_date_equal,
                $filters->delivery_date_not_equal
            )
            ->commentFilter($filters->commentFilter);
    }


    public function scopeOrderFilter($query, $orderKey)
    {
        if ($orderKey) {
            return $query->whereHas('order', function ($query) use ($orderKey) {
                $query->where('external_id', $orderKey)
                    ->orWhere('number', 'like', '%' . $orderKey . '%');
            });
        }
    }

    public function scopeCommentFilter($query, $comment)
    {
        if ($comment) {
            $query->where('comment', 'like', "%{$comment}%");
        }
    }

    public function scopeContractorsFilter($query, $contractorsIds)
    {
        if ($contractorsIds) {
            $orderProductsSubQuery = OrderProduct::select('order_id')
                ->whereIn('contractor_id', $contractorsIds);

            $query->whereIn('defects.order_id', $orderProductsSubQuery);
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
            $query->whereHas('orderProducts', function ($query) use ($isMainContractor) {
                $query->whereHas('contractor', function ($subQuery) use ($isMainContractor) {
                    $subQuery->where('is_main_contractor', $isMainContractor);
                });
            });
        }
    }

    public function scopeProductFilter($query, $searchTerm)
    {
        return $query->whereHas('order.orderProducts.product', function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhereRaw("JSON_SEARCH(LOWER(CAST(sku_list AS CHAR)), 'one', ?) IS NOT NULL", ["{$searchTerm}"]);
        });
    }

    public function scopeCreatedAtFilter($query, $start, $end, $equal, $notEqual)
    {
        $query->betweenDateTimeFilter('defects.created_at', $start, $end, $equal, $notEqual);
    }

    public function scopeLegalEntityFilter($query, $legalEntityIds)
    {
        if ($legalEntityIds) {
            $query->whereIn('defects.legal_entity_id', $legalEntityIds);
        }
    }

    public function scopePaymentMethodFilter($query, $paymentMethodIds)
    {
        if ($paymentMethodIds) {
            $query->whereIn('payment_method_id', $paymentMethodIds);
        }
    }

    // GPT фильтры

    public function scopeDeliveryAddressFilter($query, $deliveryAddress)
    {
        if ($deliveryAddress) {
            $query->where('delivery_address', 'like', '%' . $deliveryAddress . '%');
        }
    }

    public function scopeProductLocationFilter($query, $productLocationIds)
    {
        if ($productLocationIds) {
            $query->whereIn('defects.product_location', $productLocationIds);
        }
    }

    public function scopeReplacementTypeFilter($query, $replacementTypeIds)
    {
        if ($replacementTypeIds) {
            $query->whereIn('replacement_type', $replacementTypeIds);
        }
    }

    public function scopeMoneyRefundStatusFilter($query, $moneyRefundStatusIds)
    {
        if ($moneyRefundStatusIds) {
            $query->whereIn('money_refund_status', $moneyRefundStatusIds);
        }
    }

    public function scopeRefundTypeFilter($query, $refundTypeIds)
    {
        if ($refundTypeIds) {
            $query->whereIn('refund_type', $refundTypeIds);
        }
    }

    public function scopeDeliveryDateFilter($query, $start, $end, $equal, $notEqual)
    {
        $query->betweenDateTimeFilter('delivery_date', $start, $end, $equal, $notEqual);
    }
}
