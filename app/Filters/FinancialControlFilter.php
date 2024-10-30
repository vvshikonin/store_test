<?php
namespace App\Filters;

trait FinancialControlFilter
{
    use BaseFilter;

    public function scopeApplyFilters($query, $filters)
    {
        return $query->paymentMethodFilter($filters->payment_method_ids)
        ->paymentDateFilter(
            $filters->payment_date_start,
            $filters->payment_date_end,
            $filters->payment_date_equal,
            $filters->payment_date_not_equal
        )
        ->legalEntityFilter(
            $filters->legal_entity_ids
        );
    }

    public function scopePaymentMethodFilter($query, $paymentMethodIds)
    {
        if ($paymentMethodIds) {
            $query->whereIn('combined.payment_method_id', $paymentMethodIds);
        }
    }

    public function scopeLegalEntityFilter($query, $legalEntityIds)
    {
        if ($legalEntityIds) {
            $query->whereIn('legal_entity_id', $legalEntityIds);
        }
    }

    public function scopePaymentDateFilter($query, $start, $end, $equal, $notEqual)
    {
        $query->betweenDateTimeFilter('combined.payment_date', $start, $end, $equal, $notEqual);
    }

    public function scopeSourceNameFilter($query, $name)
    {
        $query->whereLike('', $name);
    }
    public function scopeSourceTypeFilter($query, $type)
    {
        $query->where('', $type);
    }
}
