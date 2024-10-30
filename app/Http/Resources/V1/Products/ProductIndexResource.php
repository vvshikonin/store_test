<?php

namespace App\Http\Resources\V1\Products;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\StorePositionResource;
class ProductIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // if ($this->created_at) {
        //     $created_at_date = new DateTime($this->created_at);
        //     $created_at_date = $created_at_date->format('d.m.Y');
        // } else {
        //     $created_at_date = '';
        // }

        $store_positions = $this->whenLoaded('stocks', StorePositionResource::collection($this->stocks));
        // $real_stock = $store_positions->sum('real_stock');
        // $average_price = number_format($store_positions->avg('price'), 2, ',', ' ');
        // $expected = $this->invoicePositions->sum('amount');
        // $expected = $expected - $this->invoicePositions->sum('credited');
        // $expected = $expected - $this->invoicePositions->sum('money_refund');
        // $reserved = $this->orderPositions->sum('amount');
        // $is_sale = $store_positions->sum('is_sale') ? ($store_positions->sum('is_sale') == $store_positions->count('is_sale') ? 1 : 'indeterminate') : 0;

        $invoiceProducts = $this->invoiceProducts;

        $real_stock = count($store_positions) ? $this->amount : 0;
        $free_stock = $this->free_stock;
        // $expected = $this->received !== null ? $this->received : 0;
        $expected = $invoiceProducts->sum('amount') - $invoiceProducts->sum('received') - $invoiceProducts->sum('refused');
        $reserved = $this->reserved !== null ? $this->reserved : 0;
        $saled = $store_positions->sum('saled');

        return [
            'id' => $this->id,
            'main_sku' => $this->main_sku,
            'sku_list' => json_decode($this->sku_list, true),
            'name' => $this->name,
            'contractor_names' => $this->contractors_name,
            'store_positions' => $store_positions,
            'avg_price' => number_format($this->avg_price, 2, ',', ' '),
            'real_stock' => intval($real_stock),
            'free_stock' => intval($free_stock),
            'expected' => intval($expected),
            'reserved' => intval($reserved),
            'is_sale' => $this->is_sale,
            'sale_type' => $this->sale_type,
            'sale_multiplier' => $this->sale_multiplier,
            'saled' => $saled,
            'orderPositions' => $this->productOrders->where('state', 'reserved'),
            'maintained_balance' => $this->maintained_balance ? $this->maintained_balance : 0,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d.m.Y') : ''
        ];
    }
}
