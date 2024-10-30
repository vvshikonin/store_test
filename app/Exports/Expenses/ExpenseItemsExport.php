<?php

namespace App\Exports\Expenses;

use App\Models\V1\Expenses\ExpenseItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\DateHandle;
use App\Exports\FromCollectionExport;

class ExpenseItemsExport extends FromCollectionExport implements WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    use DateHandle;

    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    /**
     * @param ExpenseItem $expenseItem
     * @return array
     */
    public function map($expense): array
    {
        // Этот массив будет содержать строки для экспорта
        $rows = [];

        // Рассчитываем общую сумму расхода
        $totalExpenseAmount = 0;
        foreach ($expense->items as $item) {
            $totalExpenseAmount += $item->amount * $item->price;
        }

        // Итерация по каждому ExpenseItem и добавление информации о Expense
        $paymentOptions = [
            'none' => 'Без системного платежа',
            'weekly' => 'Каждую неделю',
            'monthly' => 'Каждый месяц',
            'yearly' => 'Каждый год',
        ];

        $accountingMonthText = [
            '1' => 'Январь',
            '2' => 'Февраль',
            '3' => 'Март',
            '4' => 'Апрель',
            '5' => 'Май',
            '6' => 'Июнь',
            '7' => 'Июль',
            '8' => 'Август',
            '9' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь',
        ];

        foreach ($expense->items as $item)
        {
            $paymentOption = $expense->contragent->regular_payment ?? 'none';
            $paymentText = $paymentOptions[$paymentOption];

            $accountingMonth = $expense->accounting_month;
            $accountingYear = $expense->accounting_year;

            if (isset($accountingMonth) && array_key_exists($accountingMonth, $accountingMonthText))
                $accountingPeriod = $accountingMonthText[$accountingMonth] . ' ' . $accountingYear;
            else
                $accountingPeriod = 'Период не установлен';

            $row = [
                // Данные по Expense
                $expense->id,
                $this->formatIfSet($expense->created_at, 'd.m.Y H:i:s'),
                $expense->contragent->name ?? 'Без контрагента',

                // Данные о системном платеже
                $paymentText,
                $expense->contragent->user->name ?? 'Нет ответственного',

                $item->type->name ?? 'Без типа',
                $totalExpenseAmount,
                $expense->comment,
                $expense->legalEntity->name,

                $expense->paymentMethod->name,
                $this->formatIfSet($expense->payment_date, 'd.m.Y'),
                $expense->is_paid ? 'Оплачен' : 'Не оплачен',
                $accountingPeriod,

                // Данные об ExpenseItem
                $item->name,
                $item->amount,
                $item->price,
            ];

            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            // Данные по Expense
            '№ расхода',
            'Дата создания',
            'Контрагент',

            // Данные о системном платеже
            'Системный платеж',
            'Ответственный',

            'Тип расхода',
            'Общая стоимость',
            'Комментарий',
            'Юр. лицо',

            'Способ оплаты',
            'Дата оплаты',
            'Статус оплаты',
            'Период оплаты',

            // Данные об ExpenseItem
            'Наименование позиции',
            'Кол-во позиции',
            'Цена позиции',
        ];
    }

    public function columnWidths(): array
    {
        return [
            // Данные по Expense
            'A' => 10,
            'B' => 30,
            'C' => 20,

            // Данные о системном платеже
            'D' => 22,
            'E' => 25,

            'F' => 20,
            'G' => 20,
            'H' => 30,
            'I' => 30,

            'J' => 20,
            'K' => 20,
            'L' => 15,
            'M' => 15,

            // Данные об ExpenseItem
            'N' => 20,
            'O' => 15,
            'P' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        return $this->expenses;
    }
}
