<?php

namespace App\Casts\Invoices;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует числовые коды способов доставки в слова.
 */
class DeliveryTypeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === 0)
            return 'Курьером';
        else if ($value === 1)
            return 'Самовывоз';
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Курьером':
                return 0;
            case 'Самовывоз':
                return 1;
        }
    }
}
