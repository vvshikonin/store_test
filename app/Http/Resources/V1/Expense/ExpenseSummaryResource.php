<?php

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseSummaryResource extends JsonResource
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
            'accounting_month' => $this->accounting_month,
            'accounting_year' => $this->accounting_year,
            'total_income' => $this->total_income,
            'total_expenses' => $this->total_expenses,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
