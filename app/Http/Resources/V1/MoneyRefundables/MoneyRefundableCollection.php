<?php

namespace App\Http\Resources\V1\MoneyRefundables;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MoneyRefundableCollection extends ResourceCollection
{
    protected $total_refund_sum = null;
    protected $total_debt_sum = null;

    public function __construct($resource, $total_refund_sum, $total_debt_sum)
    {
        ResourceCollection::__construct($resource);
        $this->total_refund_sum = $total_refund_sum;
        $this->total_debt_sum = $total_debt_sum;
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
            'data' => MoneyRefundableResource::collection($this->resource),
            'summary' => [
                'total_refund_sum' => $this->total_refund_sum,
                'total_debt_sum' => $this->total_debt_sum,
            ]
        ];
    }
}
