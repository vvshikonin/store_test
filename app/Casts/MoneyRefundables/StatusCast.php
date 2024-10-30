<?php

namespace App\Casts\MoneyRefundables;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует числовые коды статусов возврата ДС.
 */
class StatusCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === 0)
            return 'Не сделан';
        else if ($value === 1)
            return 'Сделан';
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Не сделан':
                return 0;
            case 'Сделан':
                return 1;
        }
    }
}