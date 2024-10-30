<?php

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseItemResource extends JsonResource
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
            'expense_type_id' => $this->expense_type_id,
            'expense_type_name' => $this->expenseType->name ?? null,
            'custom_name' => $this->custom_name,
            'price' => $this->price,
            'amount' => $this->amount,
        ];
    }
}
