<?php

namespace App\Traits;

/**
 * Дополнительные  методы для обработки строк.
 */
trait StringHandle
{
    /**
     *  Если `$string` = `'null'` или `'undefined'` , преобразует в `null`.
     *
     * @param string $string
     */
    public function stringNullHandle($string)
    {
        if ($string === 'null' || $string === 'undefined')
            $string = null;

        return $string;
    }
}
