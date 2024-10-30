<?php

namespace App\Traits;



/**
 * Дополнительные  методы для обработки массивов.
 */
trait ArrayHandle
{
    /**
     *  Перебирает массив в глубину и передаёт обработку значения замыканию.
     *
     * @param array $array
     * @param callback $closure
     */
    public function deepMap(array $array, $closure)
    {
        return array_map(function ($value) use ($closure){
            if (is_array($value)) {
                return $this->deepMap($value, $closure);
            }
            return $closure($value);
        }, $array);
    }
}
