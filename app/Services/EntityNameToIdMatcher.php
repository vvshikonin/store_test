<?php

namespace App\Services;

use App\Models\V1\LegalEntity;
use App\Models\V1\PaymentMethod;
use App\Models\V1\Employee;
use App\Models\V1\Contractor;

class EntityNameToIdMatcher
{
    public function matchPaymentMethod($legalEntityName, $paymentMethodName)
    {
        // Ищем метод оплаты по имени, связанному с конкретным юридическим лицом
        $paymentMethod = PaymentMethod::where('name', $paymentMethodName)
            ->whereHas('legalEntity', function ($query) use ($legalEntityName) {
                $query->where('name', $legalEntityName);
            })
            ->first();
        return $paymentMethod ? $paymentMethod->id : null;
    }

    public function matchEmployee($name)
    {
        // Если имя не задано, возвращаем null
        if (empty($name)) {
            return null;
        }

        // Ищем сотрудника по имени с игнорированием регистра
        $employee = Employee::where('name', 'LIKE', "%{$name}%")->first();
        return $employee ? $employee->id : null;
    }

    public function matchContractor($name)
    {
        // Если имя не задано, возвращаем null
        if (empty($name)) {
            return null;
        }

        // Ищем поставщика по имени или его альтернативным именам
        $contractor = Contractor::where('name', 'LIKE', "%{$name}%")
            ->orWhere('name_list', 'LIKE', "%\"{$name}\"%") // name_list - это JSON поле
            ->first();
        return $contractor ? $contractor->id : null;
    }

    /**
     * Сопоставляет текстовое описание типа транзакции с его идентификатором в базе данных.
     *
     * @param string $transactionType Тип транзакции ("Поступление" или "Расход").
     * @return string|null Возвращает идентификатор типа транзакции или null, если сопоставление не найдено.
     */
    public function matchTransactionType($transactionType)
    {
        // Удаляем пробелы в начале и в конце строки
        $transactionType = trim($transactionType);
        $transactionType = str_replace(' ', '', $transactionType);

        $types = [
            'Поступление' => 'Out',
            'Расход' => 'In',
        ];

        return isset($types[$transactionType]) ? $types[$transactionType] : null;
    }
}
