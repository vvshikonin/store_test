<?php

namespace App\Traits;

use App\Models\V1\User;
use Http\Client\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException as ExceptionHttpException;

trait UserStamps
{
    /**
     * Загружает по связи пользователя, который создал модель.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Загружает по связи пользователя, который последний изменил модель.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Запускается при инициализации модели. Устанавливает обработчики событий Eloquent.
     *
     * @return void
     */
    public static function bootUserStamps()
    {
        // Событие перед созданием записи: устанавливает ID текущего пользователя в качестве создателя
        static::creating(function ($model) {
            $userId = auth()->id() ?: 1; // Замена null на 1, если пользователь не аутентифицирован
            $model->created_by = $userId;
            $model->updated_by = $userId;
        });

        // Событие перед обновлением записи: устанавливает ID текущего пользователя в качестве последнего обновившего
        static::updating(function ($model) {
            if (array_key_exists('updated_by', $model->attributes)) {
                $model->updated_by = auth()->id() ?: 1; // Замена null на 1, если пользователь не аутентифицирован
            }
        });
    }
}
