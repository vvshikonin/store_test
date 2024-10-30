<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use App\Exports\FromCollectionExport;
use App\Exports\Contracts\withColumnDataTyping;

class CompletedInventoryExport extends FromCollectionExport implements withColumnDataTyping
{

    public function map($data): array
    {
        return [
            $data->is_manually_added ? $data->manual_sku : $data->sku,
            $data->is_manually_added ? $data->manual_name : $data->name,
            $data->brand_name,
            $data->original_stock,
            $data->revision_stock,
            $data->difference,
        ];
    }

    public function headings(): array
    {
        return [
            'Основной артикул',
            'Название товара',
            'Бренд',
            'Реальный остаток',
            'Подсчитанный остаток',
            'Расхождение (шт.)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 80,
            'C' => 25,
            'D' => 30,
            'E' => 30,
            'F' => 30,
        ];
    }

    public function columnDataTyping()
    {
        return [
            'A' => DataType::TYPE_STRING,
        ];
    }
}
