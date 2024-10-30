<?php

namespace App\Http\Resources\V1\ContractorRefund;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractorRefundProductResource extends JsonResource
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
            'product_main_sku' => $this->invoiceProduct->product->main_sku,
            'product_name' => $this->invoiceProduct->product->name,
            'contractor_name' => $this->invoiceProduct->invoice->contractor->name,
            'avg_price' => $this->invoiceProduct->price,
            'amount' => $this->amount,
        ];
    }
}
