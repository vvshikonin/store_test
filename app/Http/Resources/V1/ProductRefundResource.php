<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\OrderProductResource;
use App\Traits\DateHandle;

class ProductRefundResource extends JsonResource
{
    use DateHandle;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        $order = $this->order;
        $order_status = $order->status;
        $order_status_group = $order_status->statusGroup;

        $order_external_id = $this->order_external_id;
        $order_number = $this->order_number;

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'refund_file' => $this->refund_file,
            'comment' => $this->comment,
            'product_location' => $this->product_location,
            'delivery_date' => $this->formatIfSet($this->delivery_date, 'Y-m-d'),
            'delivery_address' => $this->delivery_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'completed_at' => $this->formatIfSet($this->completed_at, 'd.m.Y'),
            'external_id' => $order_external_id ? $order_external_id : $order->external_id,
            'order_number' => $order_number ? $order_number : $order->number,
            'order_status_id' => $order_status->id,
            'order_status' => $order_status->name,
            'order_status_group' => $order_status_group->symbolic_code,
            'positions' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'creator' => $this->whenLoaded('creator', $this->creator),
            'updater' => $this->whenLoaded('updater', $this->updater),
        ];
    }
}
