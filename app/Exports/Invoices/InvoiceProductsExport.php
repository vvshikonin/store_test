<?php

namespace App\Exports\Invoices;

use App\Traits\DateHandle;
use App\Exports\FromQueryExport;
use App\Exports\Contracts\withColumnDataTyping;
use App\Exports\Contracts\withColumnHozirontalAlignment;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Формирует Excel выгрузку для коллекции `App\Modeles\V1\InvoiceProduct`.
 */
class InvoiceProductsExport extends FromQueryExport implements withColumnDataTyping, withColumnHozirontalAlignment
{
    use DateHandle;
    
    public function map($invoiceProducts): array
    {
        return [
            $invoiceProducts->invoice_number,
            $invoiceProducts->invoice_status,
            $this->formatIfSet($invoiceProducts->invoice_status_set_at, 'd.m.Y H:i:s'),
            $invoiceProducts->contractor_name,
            $invoiceProducts->product_sku,
            $invoiceProducts->product_brand,
            $invoiceProducts->product_name,
            $invoiceProducts->price,
            $invoiceProducts->amount,
            $invoiceProducts->received,
            $invoiceProducts->refused,
            $this->formatIfSet($invoiceProducts->received_at, 'd.m.Y H:i:s'),
            $this->formatIfSet($invoiceProducts->invoice_date, 'd.m.Y'),
            $this->formatIfSet($invoiceProducts->planned_delivery_date_from, 'd.m.Y'),
            $this->formatIfSet($invoiceProducts->planned_delivery_date_to, 'd.m.Y'),
            $invoiceProducts->invoice_delivery_type,
            $invoiceProducts->invoice_comment,
            $invoiceProducts->legal_entity_name,
            $invoiceProducts->payment_method_name,
            $invoiceProducts->invoice_payment_status,
            $invoiceProducts->invoice_payment_confirm,
            $this->formatIfSet($invoiceProducts->invoice_payment_date, 'd.m.Y'),
            $this->formatIfSet($invoiceProducts->created_at, 'd.m.Y H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'Номер счета',
            'Статус счета',
            'Дата статуса',
            'Поставщик',
            'Артикул',
            'Бренд',
            'Название',
            'Цена',
            'Количество',
            'Оприходовано',
            'Отказ',
            'Дата оприходования',
            'Дата счета',
            'Дата доставки с',
            'Дата доставки по',
            'Способ доставки',
            'Комментарий счёта',
            'Юр. лицо',
            'Способ оплаты',
            'Оплачен',
            'Оплата подтверждена',
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
            'D' => 25,
            'E' => 25,
            'F' => 20,
            'G' => 60,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 20,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 30,
            'R' => 20,
            'S' => 20,
            'T' => 10,
            'U' => 10,
            'V' => 20,
            'W' => 20,
        ];
    }

    public function columnHorizontalAlignment()
    {
        return [
            'A' => Alignment::HORIZONTAL_LEFT,
            'D' => Alignment::HORIZONTAL_LEFT,
            'F' => Alignment::HORIZONTAL_CENTER,
            'H' => Alignment::HORIZONTAL_CENTER,
            'I' => Alignment::HORIZONTAL_CENTER,
            'J' => Alignment::HORIZONTAL_CENTER,
            'K' => Alignment::HORIZONTAL_CENTER,
        ];
    }

    public function columnDataTyping()
    {
        return [
            'A' => DataType::TYPE_STRING,
            'E' => DataType::TYPE_STRING,
        ];
    }
}
