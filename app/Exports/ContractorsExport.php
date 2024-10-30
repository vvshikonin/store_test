<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class ContractorsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function map($data): array
    {
        return [
            $data->name,
            $data->marginality . '%',
            $data->is_main_contractor ? 'Да' : 'Нет',
            $data->working_conditions,
            $data->legal_entity,
            $data->min_order_amount,
            $data->pickup_time,
            $data->warehouse,
            $data->payment_delay ? 'Да' : 'Нет',
            $data->has_delivery_contract ? 'Да' : 'Нет',
    
        ];
    }

    public function headings(): array
    {
        return [
            'Поставщик',
            'Наценка',
            'Основной поставщик',
            'Условия работы',
            'Юр.лицо',
            'Мин. сумма заказа, руб.',
            'Время забора',
            'Склад',
            'Отсрочка платежа',
            'Договор доставки'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 10,
            'C' => 15,
            'D' => 35,
            'E' => 25,
            'F' => 20,
            'G' => 30,
            'H' => 35,
            'I' => 15,
            'J' => 15
        ];
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
