<?php

namespace App\Http\Resources\V1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\TransactionableResource;

class InvoiceProductResource extends JsonResource
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
            "id" => $this->id,
            "product_id" => $this->product->id,
            "sku" => $this->product->main_sku,
            'product_name' => $this->product->name,
            'brand_id' => $this->product->brand_id,
            'price' => number_format($this->price, 2, '.', ''),
            "amount" => $this->amount,
            "received" => $this->received,
            "received_at" => $this->received_at,
            "refused" => $this->refused,
            "refunded" => $this->refunded,
            "planned_delivery_date_from" => $this->planned_delivery_date_from,
            "planned_delivery_date_to" => $this->planned_delivery_date_to,
            'is_product_deleted' => $this->product->deleted_at ? true : false,
            'delivery_type' => $this->delivery_type,
        ];
    }
}
