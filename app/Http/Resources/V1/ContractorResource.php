<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractorResource extends JsonResource
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
            'name' => $this->legal_entity ? $this->name . ' (' . $this->legal_entity . ')' : $this->name,
            'simple_name' => $this->name,
            'legal_entity' => $this->legal_entity,
            'is_main_contractor' => $this->is_main_contractor,
            'working_conditions' => $this->working_conditions,
            'symbolic_code_list' => $this->symbolic_code_list ? json_decode($this->symbolic_code_list) : [],
            'marginality' => $this->marginality,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d.m.Y H:i:s') : 'Не изменялся',
            // Новые поля
            'min_order_amount' => $this->min_order_amount, // Минимальная сумма заказа
            'pickup_time' => $this->pickup_time, // Время забора
            'warehouse' => $this->warehouse, // Склад
            'payment_delay' => $this->payment_delay, // Отсрочка платежа
            'payment_delay_info' => $this->payment_delay_info, //Информация об острочке
            'has_delivery_contract' => $this->has_delivery_contract, // Есть договор доставки
        ];
    }
}
