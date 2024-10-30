<?php

namespace App\Casts\ContractorRefunds;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ContractorRefundDeliveryStatuses implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'complete':
                return 'Доставлено';
            case 'at_courier':
                return 'У курьера';
            case 'in_tc':
                return 'В ТК';
            default:
                return $value;
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'Доставлено':
                return 'complete';
            case 'У курьера':
                return 'at_courier';
            case 'В ТК':
                return 'in_tc';
        }
    }
}
