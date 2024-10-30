<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MoneyRefundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $positions = null;

        if($this->type == 0){
            $positions = $this->invoice_positions;
        }else{
            $positions = $this->order_positions;
        }

        return [
            'positions' => MoneyRefundPositionsResource::collection($positions)
        ];
    }
}