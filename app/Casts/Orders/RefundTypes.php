<?php

namespace App\Casts\Invoices;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class RefundTypes implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 0:
                return 'Поставщику';
            case 1:
                return 'На склад';
            case 2:
                return 'Возврат брак';
            default:
                return "id: $value";
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Поставщику':
                return 0;
            case 'На склад':
                return 1;
            case 'Возврат брак':
                return 2;
        }
    }
}
