<?php

namespace App\Exports\Expenses;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExpenseSummaryRegularExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $summaries;
    protected $expenseTypes;

    public function __construct($summaries)
    {
        $this->summaries = $summaries;
        $this->expenseTypes = \App\Models\V1\Expenses\ExpenseType::orderBy('sort_order')->pluck('name')->toArray();
    }

    public function collection()
    {
        return collect($this->summaries);
    }

    public function headings(): array
    {
        return array_merge(['Период', 'Прибыль по дате доставки (исходной)', 'Операционные расходы', 'Финансовый результат'], $this->expenseTypes);
    }

    public function map($summary): array
    {
        $row = [
            $this->getMonthName($summary->accounting_month) . ' ' . $summary->accounting_year,
            $summary->total_income,
            $summary->total_expenses != 0 ? -$summary->total_expenses : $summary->total_expenses,
            $summary->total_income - $summary->total_expenses,
        ];

        foreach ($this->expenseTypes as $type) {
            $expense = $summary->expenses_by_type[$type] ?? 0;
            $row[] = $expense != 0 ? -$expense : $expense;
        }

        return $row;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 13,
            'B' => 13,
            'C' => 13,
            'D' => 13,
        ];

        $column = 'E';
        foreach ($this->expenseTypes as $type) {
            $widths[$column] = 13;
            $column++;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        // Устанавливаем стили для заголовков
        $lastColumn = chr(ord('A') + count($this->expenseTypes) + 3); // Определяем последнюю колонку
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFC2D69A'],
            ],
        ]);

        // Устанавливаем стили для первой колонки
        $sheet->getStyle('A:A')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Устанавливаем стили для всех ячеек
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
            ],
        ]);

        // Устанавливаем меньший шрифт для всех ячеек, кроме первой колонки
        $sheet->getStyle('B:Z')->applyFromArray([
            'font' => ['size' => 9],
        ]);

        // Заливаем не пустые ячейки с ценой зелёным цветом и устанавливаем денежный формат
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if (is_numeric($cell->getValue()) && $cell->getValue() != 0) {
                    $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050');
                    $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00" ₽"');
                }
            }
        }

        // Устанавливаем стили для заголовков колонок B, C и D
        $sheet->getStyle('B1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEAF1DD'],
            ],
            'font' => [
                'color' => ['argb' => 'FF00B050'],
            ],
        ]);

        $sheet->getStyle('C1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEAF1DD'],
            ],
            'font' => [
                'color' => ['argb' => 'FFFF0000'],
            ],
        ]);

        $sheet->getStyle('D1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEAF1DD'],
            ],
        ]);

        return [];
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        ];

        return $months[$monthNumber] ?? 'Неизвестный месяц';
    }
}
