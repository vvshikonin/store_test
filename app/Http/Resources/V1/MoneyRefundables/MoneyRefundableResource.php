<?php

namespace App\Http\Resources\V1\MoneyRefundables;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\DateHandle;

class MoneyRefundableResource extends JsonResource
{
    use DateHandle;

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
            'refundable_type' => $this->refundable_type,
            'contractor_id' => $this->contractor_id,
            'contractor_name' => $this->whenLoaded('contractor', $this->contractor?->name),
            'is_main_contractor' => $this->whenLoaded('contractor', $this->contractor?->is_main_contractor),
            'contragent_id' => $this->contragent_id,
            'contragent_name' => $this->whenLoaded('contragent', $this->contragent?->name),
            'payment_method_id' => $this->payment_method_id,
            'payment_method_name' => $this->whenLoaded('paymentMethod', $this->paymentMethod?->name),
            'legal_entity_id' => $this->legal_entity_id,
            'legal_entity_name' => $this->whenLoaded('legalEntity', $this->legalEntity?->name),
            'status' => $this->status,
            'converted_to_expense_at' => $this->converted_to_expense_at,
            'converted_to_expense_id' => $this->converted_to_expense_id,
            'approved' => $this->approved,
            'reason' => $this->reason,
            'comment' => $this->comment,
            'debt_sum' => $this->debt_sum,
            'refund_sum_money' => $this->refund_sum_money,
            'refund_sum_products' => $this->refund_sum_products,
            'refundable' => $this->refundable,
            'refund_doc_file' => $this->refund_doc_file,
            'completed_at' => $this->formatIfSet($this->completed_at, 'Y-m-d'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => $this->whenLoaded('creator', $this->creator),
            'updater' => $this->whenLoaded('updater', $this->updater),
            'incomes' => $this->incomes,
        ];
    }
}
