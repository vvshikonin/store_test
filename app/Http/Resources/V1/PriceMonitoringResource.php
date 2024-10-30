<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceMonitoringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'main_sku' => $this->product->main_sku,
            'name' => $this->product->name,
            'rrp' => $this->product->RRP,
            'url' => $this->url,
            'xpath' => $this->xpath,
            'parsed_price' => $this->parsed_price
        ];
    }
}

