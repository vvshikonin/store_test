<?php

namespace App\Casts\Invoices;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MoneyRefundStatuses implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 0:
                return 'Без возврата средств';
            case 1:
                return 'Требует возврата средств';
            default:
                return "id: $value";
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Без возврата средств':
                return 0;
            case 'Требует возврата средств':
                return 1;
        }
    }
}
