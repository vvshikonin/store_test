<?php

namespace App\Exports;

use App\Models\V1\FinancialControl;
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

class FinancialControlExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->payment_date,
            $data->legal_entity_name,
            $data->payment_method_name,
            $data->manual_sum,
            $data->auto_sum,
            $data->difference,
        ];
    }

    public function headings(): array
    {
        return [
            'Дата оплаты',
            'Юр. лицо',
            'Способ оплаты',
            'Ручная сумма',
            'Авто сумма',
            'Разница',
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
            'A' => 15,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 25,
            'F' => 20
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
