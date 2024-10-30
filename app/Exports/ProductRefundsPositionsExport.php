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
use DateTime;

class ProductRefundsPositionsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
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
            $data->order_status_name,
            $data->status,
            $this->formatIfSet($data->delivery_date, 'd.m.Y'),
            $data->delivery_address,
            $data->product_location,
            $data->comment,
            $this->formatIfSet($data->created_at, 'd.m.Y'),
            $this->formatIfSet($data->completed_at, 'd.m.Y'),

            $data->main_sku,
            $data->name,
            $data->contractor_name,
            $data->avg_price,
            $data->amount,
        ];
    }

    public function headings(): array
    {
        return [
            'Номер заказа',
            'Статус заказа',
            'Статус возврата',
            'Дата возврата',
            'Адрес возврата',
            'Где товар',
            'Комментарий',
            'Создан',
            'Завершен',

            'Артикул',
            'Название',
            'Поставщик',
            'Цена',
            'Количество'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 40,
            'L' => 20,
            'M' => 20,
            'N' => 20,
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

    public function columnFormats(): array
    {
        return [
            // 'B' => DataType::TYPE_STRING,
            // 'E' => '#,##0.00 [$₽-x-ru-RU]',
            // 'F' => '#,##0.00 [$₽-x-ru-RU]',
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'J') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }


    public function collection()
    {
        return $this->data;
    }
}
