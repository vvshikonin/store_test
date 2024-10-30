<?php

namespace App\Exports;

use App\Models\V1\Product;
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
use App\Traits\DateHandle;

class ProductsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
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
            $data->main_sku,
            $data->sku_list,
            $data->name,
            number_format($data->avg_price, 2, ',', ''),
            $data->brand_name,
            $data->contractors_name,
            $data->amount,
            $data->free_stock,
            $data->expected,
            $data->reserved,
            $data->orders_info,
            $data->saled,
            $data->min_price,
            $this->formatIfSet($data->min_price_date, 'd.m.Y'),
            $data->min_price_all_time,
            $this->formatIfSet($data->min_price_all_time_date, 'd.m.Y'),
            $data->maintained_balance,
            $data->is_sale ? "Да": null,
        ];
    }

    public function headings(): array
    {
        return [
            'Основной артикул',
            'Список артикулов',
            'Название товара',
            'Средняя цена',
            'Бренд',
            'Поставщики',
            'Реальный остаток',
            'Свободный остаток',
            'Ожидается',
            'Зарезервировано',
            'Заказы резерва',
            'Продано',
            'Мин. цена закупки по остатку',
            'Дата закупки по мин. цене по остатку',
            'Мин. цена закупки за все время',
            'Дата закупки по мин. цене за все время',
            'Поддерживаемый остаток',
            'Распродажа'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 80,
            'D' => 15,
            'E' => 20
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

    public function bindValue(Cell $cell, $value)
    {
        $skuColumn = ['B']; 

        if (!in_array($cell->getColumn(), $skuColumn)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }
}
