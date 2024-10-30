<?php

namespace App\Http\Resources\V1\FinancialControl;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FinancialControlCollection extends ResourceCollection
{
    private $totalAutoSum;
    private $totalManualSum;

    public function __construct($resource, $totalAutoSum, $totalManualSum)
    {
        parent::__construct($resource);
        $this->totalAutoSum = $totalAutoSum;
        $this->totalManualSum = $totalManualSum;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($financialControl) {
                return new FinancialControlResource($financialControl);
            }),
            'totalAutoSum' => $this->totalAutoSum,
            'totalManualSum' => $this->totalManualSum,
            'totalDifference' => $this->totalManualSum - $this->totalAutoSum,
        ];
    }
}
