<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\V1\Expenses\ExpenseType;
use App\Models\V1\Expenses\ExpenseItem;
use App\Traits\DateHandle;

class AllExpenseTypesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    use DateHandle;

    public function collection()
    {
        $expenseTypes = ExpenseType::with(['expenses' => function ($query) {
            // Здесь вы можете применить любые фильтры к расходам, если это необходимо
        }])->get();

        // Здесь вы можете добавить логику для обработки типов расходов и добавления информации об отсутствующих расходах
        // Возможно, вам понадобится дополнительная логика для группировки и сортировки данных

        return $expenseTypes;
    }

    public function map($expenseType): array
    {
        // Пример маппинга данных. Адаптируйте под вашу структуру и требования
        return [
            $expenseType->name,
            // Другие поля...
        ];
    }

    public function headings(): array
    {
        return [
            // Заголовки столбцов
        ];
    }

    public function columnWidths(): array
    {
        return [
            // Ширина столбцов
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Стили
        ];
    }
}
