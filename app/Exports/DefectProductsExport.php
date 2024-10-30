<?php

namespace App\Exports;

use App\Models\V1\Defect;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DefectProductsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder, WithColumnFormatting
{
    protected $data;

    protected $product_locations = [
        0 => "Нет",
        1 => "В офисе",
        2 => "У курьера",
        3 => "Частично - Офис/Курьер",
        4 => "Частично - Офис/Поставщик",
        5 => "Частично - Поставщик/Курьер",
        6 => "Вернули поставщику",
        7 => "В ТК",
    ];

    protected $money_refund_statuses = [
        0 => "Без возврата средств",
        1 => "Требует возврата средств",
    ];

    protected $refund_types = [
        0 => "Поставщику",
        1 => "На склад",
        2 => "Возврат брак",
    ];

    protected $replacement_types = [
        0 => "С выкупом",
        1 => "Без выкупа",
        2 => "Ремонт",
    ];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->number,
            $data->order_status,
            $data->contractor_names,
            $data->main_sku,
            $data->name,
            $data->avg_price,
            $data->comment,
            $data->created_at,
            $data->delivery_address,
            $data->delivery_date,
            $data->legal_entity_name,
            $data->payment_method_name,
            $this->money_refund_statuses[$data->money_refund_status] ?? '',
            $this->product_locations[$data->product_location] ?? '',
            $this->refund_types[$data->refund_type] ?? '',
            $this->replacement_types[$data->replacement_type] ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Номер заказа',
            'Статус заказа',
            'Поставщики',
            'Артикул',
            'Название товара',
            'Цена',
            'Комментарий',
            'Когда создан',
            'Адрес доставки',
            'Дата доставки',
            'Юр. лицо',
            'Способ оплаты',
            'Возврат ДС',
            'Где товар',
            'Тип возврата',
            'Тип замены',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 10,
            'C' => 20,
            'D' => 25,
            'E' => 25,
            'F' => 10,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 20,
            'N' => 10,
            'O' => 12,
            'P' => 15
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'D') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0.00 [$₽-419]',
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
