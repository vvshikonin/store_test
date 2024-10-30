<?php

namespace App\Imports;

use DateTime;
use DateInterval;

use App\Models\V1\FinancialControl;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Services\EntityNameToIdMatcher;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Log;
class FinancialControlImport implements ToCollection, WithValidation, WithMultipleSheets
{
    use Importable;

    private $errors = [];
    private $entityMatcher;

    public function __construct(EntityNameToIdMatcher $entityMatcher)
    {
        $this->entityMatcher = $entityMatcher;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            'Лист1' => $this,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Ничего не делаем, игнорируем все листы, кроме "Лист1"
    }

    /**
     * Конвертирует дату из формата Excel в формат даты MySQL.
     *
     * @param mixed $excelDate Значение даты из Excel.
     * @return string|null Форматированная дата для MySQL или null, если произошла ошибка.
     */
    function convertExcelDateToMySQLDate($excelDate)
    {
        // Проверяем, является ли значение числом
        if (is_numeric($excelDate)) {
            // Excel начинает отсчет с 1 января 1900 года (или с 1 января 1904 года)
            $baseDate = DateTime::createFromFormat('Y-m-d', '1899-12-30'); // Используем базовую дату для системы отсчета Excel
            // Добавляем количество дней, указанных в Excel
            $dateInterval = new DateInterval('P' . intval($excelDate) . 'D');
            $baseDate->add($dateInterval);
            // Возвращаем дату в формате MySQL
            return $baseDate->format('Y-m-d');
        }
        return null; // Возвращаем null, если значение не является числом
    }

    public function collection(Collection $rows)
    {
        $rowNumber = 0;

        foreach ($rows as $row) {
            $rowNumber++; // Увеличиваем номер строки на каждой итерации

            if ($row[0] == 'Юридическое лицо') {
                continue; // Пропускаем строку заголовков
            }

            $sum = $row[5];
            // Log::info("Исходная сумма: {$sum}");

            // Удаляем пробелы
            $sum = str_replace(' ', '', $sum);
            // Log::info("Сумма после удаления пробелов: {$sum}");

            // Удаление неразрывных пробелов
            $sum = str_replace("\xC2\xA0", '', $sum);

            // Заменяем запятую на точку
            $sum = str_replace(',', '.', $sum);
            // Log::info("Сумма после замены запятой на точку: {$sum}");

            // Преобразуем строку в число с плавающей точкой
            $sum = floatval($sum);
            // Log::info("Сумма после преобразования в float: {$sum}");

            // Сопоставление данных
            $paymentMethodId = $this->entityMatcher->matchPaymentMethod($row[0], $row[1]);
            $employeeId = $this->entityMatcher->matchEmployee($row[2]);
            $transactionTypeId = $this->entityMatcher->matchTransactionType($row[3]);
            $contractorId = $this->entityMatcher->matchContractor($row[6]);
            $reason = $row[7];
            $paymentDateExcel = $row[4]; // Значение даты из Excel
            $paymentDateMySQL = $this->convertExcelDateToMySQLDate($paymentDateExcel); // Преобразование в формат MySQL

            // Сбор информации об ошибках
            $errorsInRow = [];

            if (!$paymentMethodId) {
                $errorsInRow[] = "Неверный способ оплаты: '{$row[1]}'";
            }
            // if (!$employeeId) {
            //     $errorsInRow[] = "Неверный ответственный: '{$row[2]}'";
            // }
            if (!$transactionTypeId) {
                $errorsInRow[] = "Неверный тип транзакции: '{$row[3]}'";
            }
            // if (!$contractorId) {
            //     $errorsInRow[] = "Неверный поставщик: '{$row[6]}'";
            // }
            if (!$paymentDateMySQL) {
                $errorsInRow[] = "Некорректный формат даты: '{$row[4]}'";
            }

            // Если есть ошибки в строке, добавляем информацию об ошибке и пропускаем строку
            if (!empty($errorsInRow)) {
                $this->errors[] = "Строка $rowNumber: " . implode(', ', $errorsInRow);
                continue;
            }

            // Создание новой модели FinancialControl
            $financialControl = new FinancialControl([
                'payment_method_id' => $paymentMethodId,
                'sum' => $sum,
                'contractor_id' => $contractorId,
                'employee_id' => $employeeId,
                'type' => $transactionTypeId,
                'is_confirmed' => 1, // или другое значение по умолчанию
                'confirmed_sum' => $sum, // или другая логика подтверждения
                'payment_date' => $paymentDateMySQL,
                'reason' => $reason,
            ]);

            // Сохранение модели
            $financialControl->save();
        }
    }

    public function rules(): array
    {
        return [
            // Правила валидации...
        ];
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
