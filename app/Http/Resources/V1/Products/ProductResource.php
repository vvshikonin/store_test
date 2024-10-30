<?php

namespace App\Http\Resources\V1\Products;

use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\StorePositionResource;

class ProductResource extends JsonResource
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
            'id' => $this->id,
            'main_sku' => $this->main_sku,
            'sku_list' => json_decode($this->sku_list, true),
            'name' => $this->name,
            'contractor_ids' => $this->contractorIds,
            'contractor_names' => $this->contractorNames,
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brand ? $this->brand->name : null,
            'user_comment' => $this->user_comment,
            'rrp' => $this->RRP,
            'storePositions' => StorePositionResource::collection($this->stocks),
            'transactions' => $this->transactions,
            'orderPositions' => $this->productWithTrashedOrders->sortByDesc('order_id')->toArray(),
            'invoices' => $this->productInvoices->sortByDesc('id')->toArray(),
            'maintained_balance' => $this->maintained_balance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => $this->whenLoaded('creator', $this->creator),
            'updater' => $this->whenLoaded('updater', $this->updater),
            'is_sale' => $this->is_sale,
            'sale_type' => $this->sale_type,
            'sale_multiplier' => $this->sale_multiplier,
            'strikethrough_multiplier' => $this->strikethrough_multiplier,
            'avg_price' => $this->avg_price,
            'sale_history' => $this->saleHistory,
        ];
    }
}
