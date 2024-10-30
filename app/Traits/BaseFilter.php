<?php

namespace App\Traits;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Базовые методы фильтрации.
 */
trait BaseFilter
{
    /**
     * Выполняет фильтры из массива `$filters`, посредством поиска соответствующего метода в классе.
     * По ключу элемента будет произведён поиск `scope` метода в классе. Значение будет передано в качестве аргумента.
     *
     * Нпример `$filters = ['key1' => 'value1', 'key2' => 'value2', ...]` - будет вызван метод `scopeKey1($query, $value)`, где в `$value'`
     * будет передано значение `'value1'`. Далее вызывается метод `scopeKey2($query, $value)` и т.д.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFilters($query, $filters)
    {
        // Log::info('Apply Фильтры: ' . json_encode($filters));
        $class = new ReflectionClass($this);

        foreach ($filters as $key => $value) {
            $methodName = 'scope' . Str::studly($key);
            // Log::info('Apply method: ' . $methodName);

            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($methodName);
                $method->invokeArgs($this, [$query, $value]);

                // Логирование вызова каждого метода scope с его параметрами
                // Log::debug("Applied filter method: {$methodName} with value: " . json_encode($value));
            }
        }

        // Логирование итогового SQL-запроса и параметров
        // Log::debug("Final SQL query: " . $query->toSql());
        // Log::debug("With bindings: " . json_encode($query->getBindings()));

        return $query;
    }

    /**
     *Производит `WHERE LIKE` выборку.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereLike($query, $column, $value)
    {
        $query->where($column, 'LIKE', '%' . $value . '%');
    }

    /**
     * Производит `OR WHERE LIKE` выборку.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrWhereLike($query, $column, $value)
    {
        $query->orWhere($column, 'LIKE', '%' . $value . '%');
    }

    /**
     * Комплексный фильтр `between`, `equal`, `not equal`.
     * Произведит `WHERE BETWEEN` выборку, если в `$data` переданы `start` и `end`;
     * Производит `WHERE >= start` выборку, если передано только `start` или `end = null`;
     * Производит `WHERE equal` выборку, если передано только `equal`;
     * Производит `WHERE != notEqual` выборку, если передано только `notEqual`;
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $data строка в формате `json`
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenFilter($query, $column, $data)
    {
        $data = json_decode($data);

        $start = property_exists($data, 'start') ? $data->start : null;
        $end = property_exists($data, 'end') ? $data->end : null;
        $equal = property_exists($data, 'equal') ? $data->equal : null;
        $notEqual = property_exists($data, 'notEqual') ? $data->notEqual : null;

        if ($start != null && $end != null) {
            $query->whereBetween($column, [$start, $end]);
        } else if ($start != null) {
            $query->where($column, '>=', $start);
        } else if ($end != null) {
            $query->where($column, '<=', $end);
        } else if ($equal != null) {
            $query->where($column, $equal);
        } else if ($notEqual != null) {
            $query->where($column, '!=', $notEqual);
        }
    }

    /**
     * Комплексный фильтр `between`, `equal`, `not equal` для `DataTime` типа.
     * Произведит `WHERE BETWEEN` выборку, если в `$data` переданы `start` и `end`;
     * Производит `WHERE >= start` выборку, если передано только `start` или `end = null`;
     * Производит `WHERE equal` выборку, если передано только `equal`;
     * Производит `WHERE != notEqual` выборку, если передано только `notEqual`;
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $data строка в формате `json`
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDateTimeFilter($query, $column, $data)
    {
        $data = json_decode($data);

        $start = property_exists($data, 'start') ? $data->start : null;
        $end = property_exists($data, 'end') ? $data->end : null;
        $equal = property_exists($data, 'equal') ? $data->equal : null;
        $notEqual = property_exists($data, 'notEqual') ? $data->notEqual : null;

        if ($start != null && $end != null) {
            $query->whereBetween($column, [$start . ' 00:00:00', $end . ' 23:59:59']);
        } else if ($start != null) {
            $query->where($column, '>=', $start . ' 00:00:00');
        } else if ($start == null && $end != null && $equal == null && $notEqual == null) {
            $query->where($column, '<=', $end . ' 23:59:59');
        } else if ($equal != null) {
            $query->whereBetween($column, [$equal . ' 00:00:00', $equal . ' 23:59:59']);
        } else if ($notEqual != null) {
            $query->whereNotBetween($column, [$notEqual . ' 00:00:00', $notEqual . ' 23:59:59']);
        }
    }
}
