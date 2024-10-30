<?php

namespace App\Casts\Invoices;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует числовые коды статусов счёта в слова.
 */
class InvoiceStatusCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 0:
                return 'Ожидается';
            case 1:
                return 'Частично оприходован';
            case 2:
                return 'Оприходован';
            case 3:
                return 'Отменён';
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Ожидается':
                return 0;
            case 'Частично оприходован':
                return 1;
            case 'Оприходован':
                return 2;
            case 'Отменён':
                return 3;
        }
    }
}
