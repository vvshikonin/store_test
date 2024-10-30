<?php

namespace App\Models\V1\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filterable
{
    public function scopeFilter(Builder $query, array $filters): Builder;
}
