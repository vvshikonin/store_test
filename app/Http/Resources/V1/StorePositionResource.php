<?php

namespace App\Http\Resources\V1;

use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->closestPlanedDeliveryDate !== null) {
            $planed_delivery_date = new DateTime($this->closestPlanedDeliveryDate);
            $planed_delivery_date = $planed_delivery_date->format('d.m.Y');
        } else {
            $planed_delivery_date = '—';
        }

        $expected = $this->invoiceProducts->sum('amount');
        $expected = $expected - $this->invoiceProducts->sum('received');
        $expected = $expected - $this->invoiceProducts->sum('refused');

        return [
            'contractor_id' => $this->contractor_id,
            'contractor_name' => $this->contractor->name,
            'created_at' => $this->created_at,
            'expected' => $expected,
            'id' => $this->id,
            'is_sale' => $this->is_sale,
            'name' => $this->product->name,
            'price' => $this->price,
            'product_id' => $this->product_id,
            'real_stock' => $this->amount,
            'reserved' => $this->reserved,
            'saled_amount' => $this->saled,
            'sku' => $this->sku,
            'updated_at' => $this->updated_at,
            'user_comment' => $this->user_comment,
            'planed_delivery_date' => $planed_delivery_date,
            'is_profitable_purchase' => (bool) $this->is_profitable_purchase
        ];
    }
}
