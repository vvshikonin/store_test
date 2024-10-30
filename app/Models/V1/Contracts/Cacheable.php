<?php

namespace App\Models\V1\Contracts;

interface Cacheable
{
    /**
     * Возвращает ключи кеша этой модели.
     * @return array
     */
    public function cacheKeys(): array;
}
