<?php

namespace App\Http\Resources\V1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractorRefundResource extends JsonResource
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
            'invoice_product_id' => $this->id,
            'sku' => $this->product->main_sku,
            'product_name' => $this->product->name,
            'price' => $this->price,
            'received' => $this->received,
            'refunded' => $this->refunded,
            'in_stock' => $this->product->stocks->sum('amount'),
            'stocks' => $this->product->stocks,
        ];
    }
}
