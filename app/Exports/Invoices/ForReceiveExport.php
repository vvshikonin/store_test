<?php

namespace App\Exports\Invoices;

use App\Exports\FromCollectionExport;
use App\Exports\Contracts\withColumnDataTyping;
use App\Exports\Contracts\withColumnHozirontalAlignment;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Формирует Excel выгрузку для коллекции `App\Modeles\V1\InvoiceProduct`.
 */
class ForReceiveExport extends FromCollectionExport implements withColumnDataTyping, withColumnHozirontalAlignment
{
    public function map($invoiceProducts): array
    {
        return [
            $invoiceProducts->contractor_name,
            $invoiceProducts->invoice_number,
            $invoiceProducts->product_sku,
            $invoiceProducts->product_name,
            $invoiceProducts->price,
            $invoiceProducts->invoice_sum,
            $invoiceProducts->amount,
            $invoiceProducts->received,
            $invoiceProducts->refused,
        ];
    }

    public function headings(): array
    {
        return [
            'Поставщик',
            'Номер счета',
            'Артикул',
            'Название',
            'Цена',
            'Сумма счёта',
            'Количество',
            'Оприходовано',
            'Отказ',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 60,
            'E' => 25,
            'F' => 25,
            'G' => 15,
            'H' => 15,
            'I' => 15,
        ];
    }

    public function columnHorizontalAlignment()
    {
        return [
            'A' => Alignment::HORIZONTAL_LEFT,
            'D' => Alignment::HORIZONTAL_LEFT,
            'G' => Alignment::HORIZONTAL_CENTER,
            'H' => Alignment::HORIZONTAL_CENTER,
            'I' => Alignment::HORIZONTAL_CENTER,
        ];
    }

    public function columnDataTyping()
    {
        return [
            'C' => DataType::TYPE_STRING,
            'B' => DataType::TYPE_STRING
        ];
    }
}
