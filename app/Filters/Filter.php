<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    protected $params;
    protected $builder;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Применяел фильтры к переданному запросу.
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        // Итерируем массив фильтров и в кажой итерации проверяем
        // существует ли соответствующий метод в данной реализации класса.
        // Если не существует пропускаем итерацию, иначе вызываем
        // соответствующий метод класса и передаём туда аргумент.
        foreach ($this->filters() as $name => $value) {
            if (!method_exists($this, $name)) {
                continue;
            }

            if (trim($value)) {
                $this->$name($value);
            }
        }

        return $this->builder;
    }

    /**
     * Возвращает массив фильтров.
     */
    public function filters()
    {
        return $this->params;
    }
}
