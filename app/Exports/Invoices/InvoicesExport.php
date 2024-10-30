<?php

namespace App\Exports\Invoices;

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

/**
 * Формирует Excel выгрузку для коллекции `App\Modeles\V1\Invoice`.
 */
class InvoicesExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    use DateHandle;

    protected $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function map($invoice): array
    {
        return [
            $invoice->number,
            $this->formatIfSet($invoice->date, 'd.m.Y'),
            $invoice->contractor_name,
            $invoice->total_sum,
            $invoice->status,
            $this->formatIfSet($invoice->status_set_at, 'd.m.Y H:i:s'),
            $this->formatIfSet($invoice->received_at, 'd.m.Y H:i:s'),
            $this->formatIfSet($invoice->min_delivery_date, 'd.m.Y'),
            $this->formatIfSet($invoice->max_delivery_date, 'd.m.Y'),
            $invoice->delivery_type,
            $invoice->comment,
            $invoice->legal_entity_name,
            $invoice->payment_method_name,
            $invoice->payment_status,
            $invoice->payment_confirm,
            $this->formatIfSet($invoice->payment_date, 'd.m.Y'),
            $this->formatIfSet($invoice->created_at, 'd.m.Y H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'Номер',
            'Дата счета',
            'Поставщик',
            'Сумма',
            'Статус счета',
            'Дата статуса',
            'Дата оприходования',
            'Дата доставки с',
            'Дата доставки по',
            'Способ доставки',
            'Комментарий',
            'Юр. лицо',
            'Способ выкупа',
            'Оплачен',
            'Подтверждение оплаты',
            'Дата оплаты',
            'Дата и время',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 25,
            'F' => 60,
            'G' => 30,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 25,
            'O' => 25,
            'P' => 20,
            'Q' => 20,
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
        return $this->invoice;
    }
}
