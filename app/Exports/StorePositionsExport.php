<?php

namespace App\Exports;

use App\Models\V1\Product;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Http\Resources\V1\StorePositionResource;

class StorePositionsExport extends DefaultValueBinder implements FromCollection, WithMapping, WithHeadings, WithColumnWidths, WithStyles, WithCustomValueBinder
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
        ini_set('memory_limit', '5G');
    }

    public function map($data): array
    {
        return [
            $data->main_sku,
            $data->sku_list,
            $data->name,
            $data->brand_name,
            $data->contractor_name,
            number_format($data->price, 2, ',', ''),
            $data->amount,
            $data->is_profitable_purchase ? "Да" : null,
            $data->expected,
            $data->saled,
            $data->maintained_balance,
            $data->last_receive_date,
            $data->is_sale ? "Да": null,
        ];
    }

    public function headings(): array
    {
        return [
            'Основной артикул',
            'Список артикулов',
            'Название товара',
            'Бренд',
            'Поставщик',
            'Цена',
            'Реальный остаток',
            'Выгодно купили',
            'Ожидается',
            'Продано',
            'Поддерживаемый остаток',
            'Дата последнего оприходования',
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
            'E' => 15
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'A' || 'L') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }
}
