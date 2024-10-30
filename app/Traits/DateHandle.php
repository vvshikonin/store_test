<?php

namespace App\Traits;

/**
 * Дополнительные  методы для обработки дат.
 */
trait DateHandle
{
    /**
     *  Форматирует дату, если в переменной установлено значение.
     *
     * @param DateTime $date
     * @param string $format
     * @return string|null
     */
    public function formatIfSet($date, $format)
    {
        // Если дата передана в виде строки, преобразуем ее в объект DateTime
        if (is_string($date)) {
            try {
                $date = new \DateTime($date);
            } catch (\Exception $e) {
                // Если строку не удалось преобразовать в дату, возвращаем null
                return null;
            }
        }

        return isset($date) ? $date->format($format) : null;
    }
}
