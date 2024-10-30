<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует `boolean` в `string`: `'Да'|'Нет'`.
 */
class YesNoCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === 0)
            return 'Нет';
        elseif ($value === 1)
            return 'Да';
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Нет':
                return 0;
            case 'Да':
                return 1;
        }
    }
}
