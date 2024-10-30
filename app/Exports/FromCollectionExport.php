<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

abstract class FromCollectionExport extends Export implements FromCollection
{
    protected $data;

    /**
     * Принимает для экспорта объект Collection
     * 
     * @param Illuminate\Support\Collection $data
     */

    public function __construct(\Illuminate\Support\Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }
}
