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
class ForControlExport extends FromCollectionExport implements withColumnDataTyping, withColumnHozirontalAlignment
{
    public function map($invoiceProducts): array
    {
        return [
            $invoiceProducts->contractor_name,
            $invoiceProducts->invoice_number,
            $invoiceProducts->product_sku,
            $invoiceProducts->price,
            $invoiceProducts->invoice_received_sum,
            $invoiceProducts->amount,
            $invoiceProducts->received,
            $invoiceProducts->refused,
            $invoiceProducts->invoice_delivery_type,
            $invoiceProducts->legal_entity_name,
            $invoiceProducts->payment_method_name,
        ];
    }

    public function headings(): array
    {
        return [
            'Поставщик',
            'Номер счета',
            'Артикул',
            'Цена',
            'Сумма оприхода',
            'Количество',
            'Оприходовано',
            'Отказ',
            'Способ доставки',
            'Юр. лицо',
            'Способ оплаты',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 25,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 25,
            'J' => 25,
            'K' => 25,
        ];
    }

    public function columnHorizontalAlignment()
    {
        return [
            'A' => Alignment::HORIZONTAL_LEFT,
            'D' => Alignment::HORIZONTAL_LEFT,
            'F' => Alignment::HORIZONTAL_CENTER,
            'G' => Alignment::HORIZONTAL_CENTER,
            'H' => Alignment::HORIZONTAL_CENTER,
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
