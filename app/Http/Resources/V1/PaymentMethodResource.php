<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'legal_entity_id' => $this->legal_entity_id,
            'name' => $this->name,
            'type' => $this->type,
            'legal_entity_name' => $this->legalEntity->name,
        ];
    }
}
