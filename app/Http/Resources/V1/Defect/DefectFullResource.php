<?php

namespace App\Http\Resources\V1\Defect;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\OrderProductResource;

class DefectFullResource extends JsonResource
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
            'order_number' => $this->order->number,
            'order_products' => OrderProductResource::collection($this->order->orderProducts),
            'comment' => $this->comment,
            'files' => $this->files,
            'money_refund_status' => $this->money_refund_status,
            'refund_type' => $this->refund_type,
            'product_location' => $this->product_location,
            'avg_price' => $this->avg_price,
            'delivery_date' => $this->delivery_date,
            'delivery_address' => $this->delivery_address,
            'replacement_type' => $this->replacement_type,
            'legal_entity_id' => $this->legal_entity_id,
            'payment_method_id' => $this->payment_method_id,
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
            'is_completed' => $this->is_completed,
            'creator' => $this->creator,
            'updater' => $this->updater,
            'updated_at' => $this->updated_at,
        ];
    }
}
