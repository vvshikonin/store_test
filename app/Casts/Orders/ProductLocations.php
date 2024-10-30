<?php

namespace App\Casts\Invoices;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ProductLocations implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 0:
                return 'Нет';
            case 1:
                return 'В офисе';
            case 2:
                return 'У курьера';
            case 3:
                return 'Частично - Офис/Курьер';
            case 4:
                return 'Частично - Офис/Поставщик';
            case 5:
                return 'Частично - Поставщик/Курьер';
            case 6:
                return 'Вернули поставщику';
            case 7:
                return 'В ТК';
            default:
                return "id: $value";
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Нет':
                return 0;
            case 'В офисе':
                return 1;
            case 'У курьера':
                return 2;
            case 'Частично - Офис/Курьер':
                return 3;
            case 'Частично - Офис/Поставщик':
                return 4;
            case 'Частично - Поставщик/Курьер':
                return 5;
            case 'Вернули поставщику':
                return 6;
            case 'В ТК':
                return 7;
        }
    }
}
