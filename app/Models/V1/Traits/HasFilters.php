<?php

namespace App\Models\V1\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasFilters
{
    /**
     * Добавляет выборку по фильтрам в запрос.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, array $filters)
    {
        // Создаёт через Laravel Service Container экземпляр класса фильтров,
        // который записан в свойcтве filters класса модели и применяет
        // метод apply в этом классе для применения фильтров к запросу
        return app($this->filters)->apply($query, $filters);
    }
}
