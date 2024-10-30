<?php

namespace App\Casts\Invoices;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ReplacementTypes implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 0:
                return 'С выкупом';
            case 1:
                return 'Без выкупа';
            case 2:
                return 'Ремонт';
            default:
                return "id: $value";
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'С выкупом':
                return 0;
            case 'Без выкупа':
                return 1;
            case 'Ремонт':
                return 2;
        }
    }
}
