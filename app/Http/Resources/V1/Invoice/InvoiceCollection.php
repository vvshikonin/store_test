<?php

namespace App\Http\Resources\V1\Invoice;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InvoiceCollection extends ResourceCollection
{
    protected $totalSum = null;
    protected $receivedSum = null;
    protected $refusedSum = null;
    protected $expectedSum = null;

    public function __construct($resource, $totalSum, $receivedSum, $refusedSum, $expectedSum){
        ResourceCollection::__construct($resource);
        $this->totalSum = $totalSum;
        $this->receivedSum = $receivedSum;
        $this->refusedSum = $refusedSum;
        $this->expectedSum = $expectedSum;
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
                'total_sum' => $this->totalSum,
                'received_sum' => $this->receivedSum,
                'refused_sum' => $this->refusedSum,
                'expected_sum' => $this->expectedSum,
            ]
        ];
    }
}
