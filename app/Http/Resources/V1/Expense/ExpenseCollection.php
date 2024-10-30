<?php

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ExpenseCollection extends ResourceCollection
{
    protected $totalSum = null;

    public function __construct($resource, $totalSum)
    {
        ResourceCollection::__construct($resource);
        $this->totalSum = $totalSum;
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
            'data' => $this->collection,
            'summary' => [
                'total_sum' => $this->totalSum
            ]
        ];
    }
}
