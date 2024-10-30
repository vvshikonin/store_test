<?php

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ExpenseSummaryCollection extends ResourceCollection
{
    public function __construct($resource)
    {
        ResourceCollection::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection
        ];
    }
}
