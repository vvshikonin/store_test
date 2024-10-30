<?php

namespace App\Exports;

use App\Http\Resources\V1\Invoice\InvoiceIndexResource;
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
use DateTime;

class InventoryExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->is_manually_added ? $data->manual_sku : $data->sku,
            $data->is_manually_added ? $data->manual_name : $data->name,
            $data->brand_name,
        ];
    }

    public function headings(): array
    {
        return [
            'Основной артикул',
            'Название товара',
            'Бренд',
            'Подсчитанный остаток',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 80,
            'D' => 25,
            'C' => 30,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'A') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        return $this->data;
    }
}
