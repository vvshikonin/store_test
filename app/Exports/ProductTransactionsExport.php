<?php

namespace App\Exports;

use App\Models\V1\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductTransactionsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->created_at,
            $data->transactionable_type,
            $data->user_name,
            $data->amount ? $data->amount : '0',
            $data->total_amount_at_transaction ? $data->total_amount_at_transaction : '0',
            $data->amount_at_transaction ? $data->amount_at_transaction : '0',
            $data->stock_contractor_name,
            $data->stock_price ? $data->stock_price : '0',
        ];
    }

    public function headings(): array
    {
        return [
            'Дата и время',
            'Источник',
            'Пользователь',
            'Количество',
            'Остаток у товара',
            'Остаток у позиции',
            'Поставщик',
            'Цена',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 20,
            'G' => 25,
            'H' => 15,
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }
}
