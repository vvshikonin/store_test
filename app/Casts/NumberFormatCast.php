<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует формат чисел во `float` формата: `40,00`
 */
class NumberFormatCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return number_format($value, 2, ',', '');
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return str_replace(',', '.', $value);
    }
}
