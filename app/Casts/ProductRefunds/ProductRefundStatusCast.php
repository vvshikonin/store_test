<?php

namespace App\Casts\ProductRefunds;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует числовые коды статусов возврата товаров.
 */
class ProductRefundStatusCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === 0)
            return 'Не завершен';
        else if ($value === 1)
            return 'Завершен';
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Не завершен':
                return 0;
            case 'Завершен':
                return 1;
        }
    }
}