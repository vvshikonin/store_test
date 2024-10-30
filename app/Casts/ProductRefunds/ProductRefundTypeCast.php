<?php

namespace App\Casts\ProductRefunds;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует числовые коды типов возврата товаров.
 */
class ProductRefundTypeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === 0)
            return 'На склад';
        else if ($value === 1)
            return 'Поставщику';
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'На склад':
                return 0;
            case 'Поставщику':
                return 1;
        }
    }
}