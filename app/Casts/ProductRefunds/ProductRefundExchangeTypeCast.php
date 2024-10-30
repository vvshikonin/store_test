<?php

namespace App\Casts\ProductRefunds;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует числовые коды статусов возврата товаров.
 */
class ProductRefundExchangeTypeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === 0)
            return 'От клиента';
        else if ($value === 1)
            return 'Со склада';
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'От клиента':
                return 0;
            case 'Со склада':
                return 1;
        }
    }
}