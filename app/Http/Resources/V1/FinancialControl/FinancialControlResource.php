<?php

namespace App\Http\Resources\V1\FinancialControl;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialControlResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'payment_method_id' => $this->payment_method_id,
            'payment_method_name' => $this->payment_method_name,
            'legal_entity_id' => $this->legal_entity_id,
            'legal_entity_name' => $this->legal_entity_name,
            'manual_sum' => $this->manual_sum,
            'auto_sum' => $this->auto_sum,
            'payment_date' => $this->payment_date
        ];
    }
}
