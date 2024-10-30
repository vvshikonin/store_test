<?php

namespace App\Filters;

use App\Models\V1\Product;
use App\Models\V1\OrderProduct;
use App\Models\V1\PaymentMethod;
use App\Models\V1\ProductRefund;


trait ProductRefundFilter
{
    use BaseFilter;

    public function scopeApplyFilters($query, $filters)
    {
        $query->productRefundsPositionFilter($filters['productFilter'])
            ->contractorFilter($filters['contractorFilter'])
            ->refundTypeFilter($filters['typeFilter'])
            ->refundStatusFilter($filters['statusFilter'])
            ->legalEntityFilter($filters['legalEntityFilter'])
            ->paymentMethodFilter($filters['paymentMethodFilter'])
            ->paymentTypeFilter($filters['paymentTypeFilter'])
            ->exchangeTypeFilter($filters['exchangeTypeFilter'])
            ->productLocationFilter($filters['productLocationFilter'])
            ->orderNumberFilter($filters['orderNumberFilter'])
            ->commentFilter($filters['commentFilter'])
            ->addressFilter($filters['addressFilter'])
            ->amountFilter(
                $filters['amount_start'],
                $filters['amount_end'],
                $filters['amount_equal'],
                $filters['amount_notEqual']
            )->priceFilter(
                $filters['price_start'],
                $filters['price_end'],
                $filters['price_equal'],
                $filters['price_notEqual']
            )->deliveryDateFilter(
                $filters['delivery_date_start'],
                $filters['delivery_date_end'],
                $filters['delivery_date_equal'],
                $filters['delivery_date_notEqual']
            )->createdAtFilter(
                $filters['created_at_start'],
                $filters['created_at_end'],
                $filters['created_at_equal'],
                $filters['created_at_notEqual']
            )->completedAtFilter(
                $filters['completed_at_start'],
                $filters['completed_at_end'],
                $filters['completed_at_equal'],
                $filters['completed_at_notEqual']
            );
    }

    public function scopeProductRefundsPositionFilter($query, $product)
    {
        if ($product !== null)
        {
            $product = strtolower($product);

            $product_ids = Product::select('id')->productFilter($product);
            $position_ids = OrderProduct::select('order_products.order_id')->distinct()
                    ->whereIn('order_products.product_id', $product_ids);
            $query->whereIn('order_id', $position_ids);
        }
    }

    public function scopeContractorFilter($query, $contractor)
    {
        if ($contractor !== null)
        {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('order_products', 'product_refunds.order_id', '=', 'order_products.order_id')
                ->where('contractor_id', $contractor);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    public function scopeRefundTypeFilter($query, $type)
    {
        if ($type !== null)
        {
            $query->where('product_refunds.type', $type);
        }
    }

    public function scopeRefundStatusFilter($query, $status)
    {
        if ($status !== null)
        {
            $query->where('product_refunds.status', $status);
        }
    }

    public function scopeLegalEntityFilter($query, $legalEntity)
    {
        if ($legalEntity !== null)
        {
            $query->where('product_refunds.legal_entity_id', $legalEntity);
        }
    }

    public function scopePaymentMethodFilter($query, $paymentMethod)
    {
        if ($paymentMethod !== null)
        {
            $query->where('product_refunds.payment_method_id', $paymentMethod);
        }
    }

    public function scopePaymentTypeFilter($query, $paymentType)
    {
        if ($paymentType !== null)
        {
            $paymentIds = PaymentMethod::select('id')->where('type', $paymentType);

            $query->whereIn('product_refunds.payment_method_id', $paymentIds);
        }
    }
    
    public function scopeExchangeTypeFilter($query, $exchangeType)
    {
        if ($exchangeType !== null)
        {
            $query->where('product_refunds.exchange_type', $exchangeType);
        }    
    }

    public function scopeProductLocationFilter($query, $location)
    {
        if ($location !== null)
        {
            $query->whereLike('product_refunds.product_location', $location);
        }
    }

    public function scopeOrderNumberFilter($query, $order_number)
    {
        if ($order_number !== null)
        {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('orders', 'orders.id', '=', 'product_refunds.order_id')
                ->whereLike('orders.number', $order_number)
                ->orWhere('orders.external_id', $order_number);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    public function scopeCommentFilter($query, $comment)
    {
        if ($comment !== null)
        {
            $query->whereLike('product_refunds.comment', $comment);
        }
    }

    public function scopeAddressFilter($query, $address)
    {
        if ($address !== null)
        {
            $query->whereLike('product_refunds.delivery_address', $address);
        }
    }

    public function scopeAmountFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null || $end !== null || $equal !== null || $not_equal !== null)
        {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('order_products', 'product_refunds.order_id', '=', 'order_products.order_id')
                ->betweenFilter('amount', $start, $end, $equal, $not_equal);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    public function scopePriceFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null || $end !== null || $equal !== null || $not_equal !== null)
        {
            $order_ids = ProductRefund::select('order_id')->distinct()
                ->leftJoin('order_products', 'product_refunds.order_id', '=', 'order_products.order_id')
                ->betweenFilter('avg_price', $start, $end, $equal, $not_equal);
            $query->whereIn('product_refunds.order_id', $order_ids);
        }
    }

    public function scopeDeliveryDateFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null || $end !== null || $equal !== null || $not_equal !== null)
        {
            $query->betweenDateTimeFilter('product_refunds.delivery_date', $start, $end, $equal, $not_equal);
        }
    }

    public function scopeCreatedAtFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null || $end !== null || $equal !== null || $not_equal !== null)
        {
            $query->betweenDateTimeFilter('product_refunds.created_at', $start, $end, $equal, $not_equal);
        }
    }

    public function scopeCompletedAtFilter($query, $start, $end, $equal, $not_equal)
    {
        if ($start !== null || $end !== null || $equal !== null || $not_equal !== null)
        {
            $query->betweenDateTimeFilter('product_refunds.completed_at', $start, $end, $equal, $not_equal);
        }
    }
}