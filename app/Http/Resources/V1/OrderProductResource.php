<?php

namespace App\Http\Resources\V1;

use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $id = $this->id;
        $order_id = $this->order_id;
        $external_id = $this->external_id;

        $created_at = $this->created_at;
        $updated_at = $this->updated_at;
        $deleted_at = $this->deleted_at;

        $created_at = $created_at !== null ? (new DateTime($created_at))->format('d.m.Y') : null;
        $updated_at = $updated_at !== null ? (new DateTime($updated_at))->format('d.m.Y') : null;
        $deleted_at = $deleted_at !== null ? (new DateTime($deleted_at))->format('d.m.Y') : null;

        $product = $this->product;
        $contractor = $this->contractor;
        $product_main_sku = $product ? $product->main_sku : null;
        $product_sku_list = $product ? $product->sku_list : null;
        $product_name = $product ? $product->name : null;
        $product_id = $product ? $product->id : null;
        $contractor_name = $contractor ? $contractor->name : null;
        $contractor_id = $contractor ? $contractor->id: null ;
        $avg_price = $this->avg_price;
        $amount = $this->amount;

        return [
            'id' => $id,
            'order_id' => $order_id,
            'external_id' => $external_id,
            'amount' => $amount,
            'avg_price' => $avg_price,
            'product_id' => $product_id,
            'product_main_sku' => $product_main_sku,
            'product_sku_list' => $product_sku_list,
            'product_name' => $product_name,
            'contractor_id' => $contractor_id,
            'contractor_name' => $contractor_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at
        ];
    }
}
