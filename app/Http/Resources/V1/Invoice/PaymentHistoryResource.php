<?php

namespace App\Http\Resources\V1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует ответ `json` для `App\Model\V1\InvoicePaymentHistory`.
 */
class PaymentHistoryResource extends JsonResource
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
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'legal_entity' => $this->legalEntity,
            'payment_date' => $this->payment_date,
            'user' => $this->user,
            'created_at' => $this->created_at,
        ];
    }
}
