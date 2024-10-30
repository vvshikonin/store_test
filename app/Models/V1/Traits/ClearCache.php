<?php

namespace App\Models\V1\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\V1\Contracts\Cacheable;

/**
 * Реализует механизм очистки кэша при изменении данных в БД.
 */
trait ClearCache
{
    public static function bootCacheable()
    {
        if (!in_array(Cacheable::class, class_implements(self::class))) {
            throw new \Exception(get_called_class() . ' должен имплементировать интерфейс ' . Cacheable::class . '.');
        }

        static::updated(function ($model) {
            foreach ($model->cacheKeys() as $key) {
                Cache::forget($key);
            }
        });

        static::created(function ($model) {
            foreach ($model->cacheKeys() as $key) {
                Cache::forget($key);
            }
        });

        static::deleted(function ($model) {
            foreach ($model->cacheKeys() as $key) {
                Cache::forget($key);
            }
        });
    }
}
