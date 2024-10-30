<?php

namespace App\Filters;

trait BaseFilter
{
    function scopeWhereLike($query, $column, $value)
    {
        $query->where($column, 'LIKE', '%' . $value . '%');
    }
    function scopeOrWhereLike($query, $column, $value)
    {
        $query->orWhere($column, 'LIKE', '%' . $value . '%');
    }

    function scopeBetweenFilter($query, $column, $start, $end, $equal, $notEqual)
    {
        if ($start !== null && $end !== null) {
            $query->whereBetween($column, [$start, $end]);
        } else if ($start !== null) {
            $query->where($column, '>=', $start);
        } else if ($start === null && $end !== null && $equal === null && $notEqual === null) {
            $query->where($column, '<=', $end);
        } else if ($equal !== null) {
            $query->where($column, $equal);
        } else if ($notEqual !== null) {
            $query->where($column, '!=', $notEqual);
        }
    }
    function scopeOrBetweenFilter($query, $column, $start, $end, $equal, $notEqual)
    {
        if ($start !== null && $end !== null) {
            $query->orWhereBetween($column, [$start, $end]);
        } else if ($start !== null) {
            $query->orWhere($column, '>=', $start);
        } else if ($start === null && $end !== null && $equal === null && $notEqual === null) {
            $query->orWhere($column, '<=', $end);
        } else if ($equal !== null) {
            $query->orWhere($column, $equal);
        } else if ($notEqual !== null) {
            $query->orWhere($column, '!=', $notEqual);
        }
    }

    function scopeBetweenDateTimeFilter($query, $column, $start, $end, $equal, $notEqual)
    {
        if ($start !== null && $end !== null) {
            $query->whereBetween($column, [$start . ' 00:00:00', $end . ' 23:59:59']);
        } else if ($start !== null) {
            $query->where($column, '>=', $start . ' 00:00:00');
        } else if ($start === null && $end !== null && $equal === null && $notEqual === null) {
            $query->where($column, '<=', $end . ' 23:59:59');
        } else if ($equal !== null) {
            $query->whereBetween($column, [$equal . ' 00:00:00', $equal . ' 23:59:59']);
        } else if ($notEqual !== null) {
            $query->whereNotBetween($column, [$notEqual . ' 00:00:00', $notEqual . ' 23:59:59']);
        }
    }
    function scopeOrBetweenDateTimeFilter($query, $column, $start, $end, $equal, $notEqual)
    {
        if ($start !== null && $end !== null) {
            $query->orWhereBetween($column, [$start . ' 00:00:00', $end . ' 23:59:59']);
        } else if ($start !== null) {
            $query->orWhere($column, '>=', $start . ' 00:00:00');
        } else if ($start === null && $end !== null && $equal === null && $notEqual === null) {
            $query->orWhere($column, '<=', $end . ' 23:59:59');
        } else if ($equal !== null) {
            $query->orWhereBetween($column, [$equal . ' 00:00:00', $equal . ' 23:59:59']);
        } else if ($notEqual !== null) {
            $query->orWhereNotBetween($column, [$notEqual . ' 00:00:00', $notEqual . ' 23:59:59']);
        }
    }
}
