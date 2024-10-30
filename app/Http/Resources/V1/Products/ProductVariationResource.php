<?php

namespace App\Http\Resources\V1\Products;

use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->delivery_date){
            $delivery_date = new DateTime($this->delivery_date);
            $delivery_date = $delivery_date->format('d.m.Y');
        }else{
            $delivery_date = '—';
        }

        return[
            'id' => $this->id,
            'price' => number_format($this->price, '2', ',', ' '),
            'expected' => $this->expected ? $this->expected : 0,
            'real_stock' => $this->real_stock,
            'contractor_name' => $this->contractor_name,
            'saled_amount' => $this->saled_amount,
            'user_comment' => $this->user_comment,
            'is_sale' => $this->is_sale,
            'updated_at' => $this->updated_at->format('d.m.Y'),
            'delivery_date' => $delivery_date
        ];
    }
}
