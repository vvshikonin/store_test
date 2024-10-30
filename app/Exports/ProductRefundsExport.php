<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Traits\DateHandle;

class ProductRefundsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    use DateHandle;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->order_number,
            $data->status,
            $this->formatIfSet($data->delivery_date, 'd.m.Y'),
            $data->delivery_address,
            $data->product_location,
            $data->comment,
            $this->formatIfSet($data->created_at, 'd.m.Y'),
            $this->formatIfSet($data->completed_at, 'd.m.Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Номер заказа',
            'Статус',
            'Дата возврата',
            'Адрес возврата',
            'Где товар',
            'Комментарий',
            'Создан',
            'Завершен'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 40,
            'E' => 30,
            'F' => 50,
            'G' => 20,
            'H' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],
        ];
    }

    public function collection()
    {
        return $this->data;
    }
}
