<?php

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V1\MoneyRefundable;

class ExpenseResource extends JsonResource
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
            'comment' => $this->comment,
            'payment_method_id' => $this->paymentMethod ?? null,
            'legal_entity_id' => $this->legalEntity ?? null,
            'payment_date' => $this->payment_date,
            'is_paid' => $this->is_paid,
            'is_need_to_complete' => $this->is_need_to_complete,
            'is_edo' => $this->is_edo,
            'files' => $this->files,
            'items' => ExpenseItemResource::collection($this->items),
            'converted_to_money_refunds_id' => $this->converted_to_money_refunds_id,
            'contragent_id' => $this->contragent,
            'accounting_month' => $this->accounting_month,
            'accounting_year' => $this->accounting_year,
            'created_by' => $this->created_by,
            'creator' => $this->creator,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
