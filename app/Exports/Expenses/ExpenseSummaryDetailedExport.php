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

class ExpenseSummaryDetailedExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $summaries;
    protected $expenseTypes;

    public function __construct($summaries)
    {
        $this->summaries = $summaries;
        $this->expenseTypes = \App\Models\V1\Expenses\ExpenseType::orderBy('sort_order')->pluck('name')->toArray();
        ini_set('memory_limit', '512M');
    }

    public function collection()
    {
        return collect($this->summaries);
    }

    public function headings(): array
    {
        return array_merge(
            ['Период и дата оплаты', 'Прибыль по дате доставки (исходной)', 'Операционные расходы', 'Финансовый результат'],
            $this->expenseTypes
        );
    }

    public function map($summary): array
    {
        $rows = [];

        // Добавляем строку с периодом
        $periodRow = [
            $this->getMonthName($summary->accounting_month) . ' ' . $summary->accounting_year,
            $summary->total_income,
            $summary->total_expenses != 0 ? -$summary->total_expenses : $summary->total_expenses,
            $summary->total_income - $summary->total_expenses,
        ];

        foreach ($this->expenseTypes as $type) {
            $expense = $summary->expenses_by_type[$type]['total'] ?? 0;
            $periodRow[] = $expense != 0 ? -$expense : $expense;
        }

        $rows[] = $periodRow;

        // Собираем все строки с датами оплат и суммами
        $paymentRows = [];
        foreach ($this->expenseTypes as $type) {
            foreach ($summary->expenses_by_type[$type]['items'] ?? [] as $item) {
                $itemRow = [
                    \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(\Carbon\Carbon::parse($item['date'])),
                    '', '', '', // Пустые ячейки для итоговых сумм
                ];

                foreach ($this->expenseTypes as $innerType) {
                    if ($innerType === $type) {
                        $itemRow[] = $item['price'] != 0 ? -$item['price'] : $item['price'];
                    } else {
                        $itemRow[] = '';
                    }
                }

                $paymentRows[] = $itemRow;
            }
        }

        // Сортируем строки с датами оплат по дате
        usort($paymentRows, function ($a, $b) {
            return $a[0] <=> $b[0];
        });

        // Добавляем отсортированные строки с датами оплат
        $rows = array_merge($rows, $paymentRows);

        // Добавляем пустую строку для разделения периодов
        $emptyRow = array_fill(0, count($this->expenseTypes) + 4, '');
        $rows[] = $emptyRow;

        return $rows;
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
                'startColor' => ['argb' => 'C2D69A'],
            ],
        ]);

        // Закрепляем первую строку с заголовками
        $sheet->freezePane('A2');

        // Устанавливаем стили для первой колонки
        $sheet->getStyle('A:A')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
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
            ],
        ]);

        // Устанавливаем меньший шрифт для всех ячеек, кроме первой колонки
        $sheet->getStyle('B:Z')->applyFromArray([
            'font' => ['size' => 9],
        ]);

        // Устанавливаем меньший шрифт для ячеек под заголовком в первой колонке
        $sheet->getStyle('A2:A' . $sheet->getHighestRow())->applyFromArray([
            'font' => ['size' => 8],
        ]);

        // Устанавливаем формат даты для первой колонки
        $sheet->getStyle('A2:A' . $sheet->getHighestRow())->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);

        // Устанавливаем жирную рамку для строк с периодами
        $startRow = null;
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $firstCell = $cellIterator->current();
            if ($firstCell->getValue() && strpos($firstCell->getValue(), ' ') !== false) {
                if ($startRow !== null) {
                    $sheet->getStyle("A{$startRow}:{$lastColumn}" . ($row->getRowIndex() - 1))->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                            ],
                        ],
                    ]);
                }
                $startRow = $row->getRowIndex();
                // Заливаем первую строку каждого периода
                $sheet->getStyle("A{$startRow}:{$lastColumn}{$startRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'C2D69A'],
                    ],
                ]);
            }
        }
        if ($startRow !== null) {
            $sheet->getStyle("A{$startRow}:{$lastColumn}" . ($sheet->getHighestRow() - 1))->applyFromArray([
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                    ],
                ],
            ]);
        }

        // Заливаем не пустые ячейки с ценой зелёным цветом и устанавливаем денежный формат
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                // Проверяем, является ли ячейка датой
                $column = $cell->getColumn();
                $rowIndex = $cell->getRow();
                $isDateColumn = $column === 'A' && $rowIndex > 1; // Колонка A и не первая строка

                if (!$isDateColumn && is_numeric($cell->getValue()) && $cell->getValue() != 0) {
                    $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050');
                    $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00" ₽"');
                }
            }
        }

        // Убираем границы у пустых строк и заливаем их тёмно-серым цветом
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $isEmptyRow = true;
            foreach ($cellIterator as $cell) {
                if ($cell->getValue() !== null && $cell->getValue() !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                $sheet->getStyle($row->getRowIndex() . ':' . $row->getRowIndex())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF808080'],
                    ],
                ]);
                // Увеличиваем высоту пустой строки
                $sheet->getRowDimension($row->getRowIndex())->setRowHeight(30);
            }
        }

        // Убираем заливку и жирный шрифт для дат
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $firstCell = $cellIterator->current();
            if ($firstCell->getValue() && strpos($firstCell->getValue(), '-') !== false) {
                $sheet->getStyle('A' . $row->getRowIndex())->applyFromArray([
                    'font' => ['bold' => false],
                    'fill' => [
                        'fillType' => Fill::FILL_NONE,
                    ],
                ]);
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
