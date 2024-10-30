<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;

abstract class FromQueryExport extends Export implements FromQuery
{
    protected $query;

    /**
     * Принимает для экспорта объект Query Builder
     *
     * @param Illuminate\Database\Eloquent\BuilderBuilder $query
     */

    public function __construct(\Illuminate\Database\Eloquent\Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }
}
