<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Traits\DateHandle;

class ContractorRefundsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    use DateHandle;

    protected $refunds;

    public function __construct($refunds)
    {
        $this->refunds = $refunds;
    }

    public function map($refund): array
    {
        return [
            $refund->invoice->number,
            $refund->invoice->contractor->name,
            $refund->is_complete ? 'Завершено' : 'Не завершено',
            $refund->refund_sum,
            $refund->comment,
            $this->formatIfSet($refund->delivery_date, 'd.m.Y'),
            $refund->delivery_address,
            $refund->delivery_status,
            $this->formatIfSet($refund->created_at, 'd.m.Y H:i:s'),
            $refund->creator->name,
        ];
    }

    public function headings(): array
    {
        return [
            'Номер счёта',
            'Поставщик',
            'Статус возврата',
            'Сумма возврата',
            'Комментарий',
            'Дата доставки',
            'Адрес доставки',
            'Статус доставки',
            'Дата создания',
            'Кем создан',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 25,
            'D' => 30,
            'E' => 20,
            'F' => 25,
            'G' => 25,
            'H' => 20,
            'I' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            // 'A' => [
            //     'alignment' => [
            //         'horizontal' => Alignment::HORIZONTAL_LEFT,
            //     ],
            // ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0.00 [$₽-x-ru-RU]',
        ];
    }

    public function collection()
    {
        return $this->refunds;
    }
}
