<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MoneyRefundPositionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->order_id){
            return [
                'name' => $this->product->name,
                'sku' => $this->product->main_sku,
                // 'price' => $this->price,
                'amount' => $this->amount,
                // 'contractor_name' => $this->contractor_name
            ];
        }else{
            return [
                'name' => $this->storePosition->product->name,
                'sku' => $this->storePosition->product->main_sku,
                'price' => $this->storePosition->price,
                'amount' => $this->money_refund,
                // 'contractor_name' => $this->invoice
            ];
        }

    }
}