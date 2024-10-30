<?php

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
