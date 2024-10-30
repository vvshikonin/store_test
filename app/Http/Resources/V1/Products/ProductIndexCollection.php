<?php

namespace App\Http\Resources\V1\Products;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductIndexCollection extends ResourceCollection
{
    public $totalSum;
    public $totalStocks;
    public $totalFreeStocks;
    public $totalReservedSum;
    public $totalMaintainedBalance;
    public $totalProfitableSum;

    public function __construct($resource, $totalSum, $totalStocks, $totalFreeStocks, $totalReservedSum, $totalMaintainedBalance, $totalProfitableSum)
    {
        ResourceCollection::__construct($resource);

        $this->totalSum = $totalSum;
        $this->totalStocks = $totalStocks;
        $this->totalFreeStocks = $totalFreeStocks;
        $this->totalReservedSum = $totalReservedSum;
        $this->totalMaintainedBalance = $totalMaintainedBalance;
        $this->totalProfitableSum = $totalProfitableSum;
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
            'totalSum' => number_format($this->totalSum, '2', ',', ' '),
            'totalReservedSum' => number_format($this->totalReservedSum, '2', ',', ' '),
            'totalProfitableSum' => number_format($this->totalProfitableSum, '2', ',', ' '),
            'totalStocks' => $this->totalStocks,
            'totalFreeStocks' => $this->totalFreeStocks,
            'totalMaintainedBalance' => $this->totalMaintainedBalance,
        ];
    }
}
