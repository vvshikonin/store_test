<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Traits\ClearCache;
use App\Models\V1\Contracts\Cacheable;

class Contractor extends Model implements Cacheable
{
    use HasFactory;
    use ClearCache;

    protected $fillable = [
        'name',
        'marginality',
        'is_main_contractor',
        'symbolic_code_list',
        'working_conditions',
        'legal_entity',
        // Новые поля
        'min_order_amount', // Минимальная сумма заказа
        'pickup_time', // Время забора
        'warehouse', // Склад
        'payment_delay', // Отсрочка платежа
        'payment_delay_info', //Информация об острочке
        'has_delivery_contract', // Есть договор доставки
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function cacheKeys(): array
    {
        return [
            'contractors_all',
        ];
    }

    /**
     * Фильтр по имени поставщика.
     */
    public function scopeNameFilter($query, $nameFilter)
    {
        if ($nameFilter !== null) {
            $query->where('name', 'like', '%' . $nameFilter . '%');
        }
    }

    /**
     * Фильтр по имени поставщика.
     */
    public function scopeMarginalityFilter($query, $marginalityFilter)
    {
        if ($marginalityFilter !== null) {
            $query->where('marginality', '=', $marginalityFilter);
        }
    }

    /**
     * Фильтр по полю Основной поставщик.
     */
    public function scopeMainContractorFilter($query, $mainContractorFilter)
    {
        if ($mainContractorFilter !== null) {
            $query->where('is_main_contractor', '=', $mainContractorFilter);
        }
    }

    /**
     * Фильтр по описанию рабочих условий.
     */
    public function scopeWorkingConditionsFilter($query, $workingConditionsFilter)
    {
        if ($workingConditionsFilter !== null) {
            $query->where('working_conditions', 'like', '%' . $workingConditionsFilter . '%');
        }
    }

    /**
     * Фильтр по имени юр.лица.
     */
    public function scopeLegalEntityFilter($query, $legalEntityFilter)
    {
        if ($legalEntityFilter !== null) {
            $query->where('legal_entity', 'like', '%' . $legalEntityFilter . '%');
        }
    }

    /**
     * Фильтр по минимальной сумме заказа.
     */
    public function scopeMinOrderAmountFilter($query, $minOrderAmountFilter)
    {
        if ($minOrderAmountFilter !== null) {
            $query->where('min_order_amount', '=', $minOrderAmountFilter);
        }
    }

    /**
     * Фильтр по времени забора.
     */
    public function scopePickupTimeFilter($query, $pickupTimeFilter)
    {
        if ($pickupTimeFilter !== null) {
            $query->where('pickup_time', 'like', '%' . $pickupTimeFilter . '%');
        }
    }

    /**
     * Фильтр по адресу склада.
     */
    public function scopeWarehouseFilter($query, $warehouseFilter)
    {
        if ($warehouseFilter !== null) {
            $query->where('warehouse', 'like', '%' . $warehouseFilter . '%');
        }
    }

    /**
     * Фильтр по полю Отсрочка платежа.
     */
    public function scopePaymentDelayFilter($query, $paymentDelayFilter)
    {
        if ($paymentDelayFilter !== null) {
            $query->where('payment_delay', '=', $paymentDelayFilter);
        }
    }

    /**
     * Фильтр по полю Договор доставки.
     */
    public function scopeDeliveryContractFilter($query, $deliveryContractFilter)
    {
        if ($deliveryContractFilter !== null) {
            $query->where('has_delivery_contract', '=', $deliveryContractFilter);
        }
    }

    /**
     * Фильтр по символьным кодам (не используется).
     */
    public function scopeSymbolCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->whereJsonContains('symbolic_code_list', $code);
        }
    }
}
