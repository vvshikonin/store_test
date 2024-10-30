<?php

namespace App\Http\Resources\V1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует ответ `json` для `App\Model\V1\InvoicePaymentHistory`.
 */
class RefusesHistoryResource extends JsonResource
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
            'user_name' => $this->user_name,
            'product_id' => $this->product_id,
            'product_sku' => $this->product_sku,
            'product_name' => $this->product_name,
            'amount' => $this->amount,
            'refused_at' => $this->refused_at,
        ];
    }
}
