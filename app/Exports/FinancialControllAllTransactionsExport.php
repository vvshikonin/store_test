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

class FinancialControllAllTransactionsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->record_type == "Ручной" ? $data->payment_date : $data->created_at,
            $data->user ? $data->user->name : $data->transactionable_type,
            $data->paymentMethod ? $data->paymentMethod->legalEntity->name : '',
            $data->paymentMethod ? $data->paymentMethod->name : '',
            $data->sum,
            $data->type == 'In' ? 'Расход' : 'Поступление',
            $data->contractor ? $data->contractor->name : '',
            $data->reason,
            $data->employee ? $data->employee->name : '',
            $data->record_type,
        ];
    }

    public function headings(): array
    {
        return [
            'Дата оплаты',
            'Источник',
            'Юридическое лицо',
            'Способ оплаты',
            'Сумма',
            'Тип транзакции',
            'Поставщик',
            'Основание',
            'Ответственный',
            'Тип записи',
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
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 25,
            'H' => 25,
            'I' => 20,
            'J' => 20
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
