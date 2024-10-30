<?php

namespace App\Http\Resources\V1\Defect;

use App\Http\Resources\V1\ContractorResource;
use App\Http\Resources\V1\OrderProductResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class DefectResource extends JsonResource
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
            'order_number' => $this->order->number,
            'order_external_id' => $this->order->external_id,
            'order_status' =>  $this->order->status->name,
            'order_status_group' => $this->order->status->statusGroup->symbolic_code,
            'contractor_names' => $this->contractor_names,
            'comment' => $this->comment,
            'is_completed' => $this->is_completed,
            'money_refund_status' => $this->money_refund_status,
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
            'amount' => $this->order->orderProducts->sum('amount'),
            'legal_entity' => $this->legalEntity ? $this->legalEntity->name : null,
            'payment_method' => $this->paymentMethod ? $this->paymentMethod->name : null,
            'summ' => number_format($this->sum, 2, ',', ' '),
            'order_products' => OrderProductResource::collection($this->order->orderProducts),
            'delivery_date' => $this->delivery_date,
            'delivery_address' => $this->delivery_address,
            'replacement_type' => $this->replacement_type,
            'product_location' => $this->product_location,
            'refund_type' => $this->refund_type,
            'files' => json_encode($this->files)
        ];
    }
}
