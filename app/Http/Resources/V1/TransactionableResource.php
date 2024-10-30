<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionableResource extends JsonResource
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
            'amount' =>  $this->amount,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'product_id' => $this->stock->product->id,
            'product_sku' => $this->stock->product->main_sku,
            'product_name' => $this->stock->product->name,
            'price' => $this->stock->price,
            'type' => $this->type,
            'user_name' => $this->user->name,
        ];
    }
}
