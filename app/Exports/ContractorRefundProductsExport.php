<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Traits\DateHandle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ContractorRefundProductsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping
{
    use DateHandle;

    protected $refunds;

    public function __construct($refunds)
    {
        $this->refunds = $refunds;
    }

    public function map($refund): array
    {
        $baseData = [
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

        // Добавляем данные о продуктах
        $productsData = [];
        foreach ($refund->contractorRefundProducts as $product) {
            $productsData[] = $product->invoiceProduct->product->main_sku;
            $productsData[] = $product->invoiceProduct->product->name;
            $productsData[] = $product->invoiceProduct->price;
            $productsData[] = $product->amount;
        }

        return array_merge($baseData, $productsData);
    }

    public function headings(): array
    {
        return array_merge(
            [
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
            ],
            [
                'Артикул',
                'Название',
                'Цена',
                'Количество',
            ]
        );
    }

    public function columnWidths(): array
    {
        return array_merge(
            [
                'A' => 25,
                'B' => 20,
                'C' => 25,
                'D' => 30,
                'E' => 20,
                'F' => 25,
                'G' => 25,
                'H' => 20,
            ],
            [
                'I' => 23,
                'J' => 25,
                'K' => 30,
                'L' => 20,
                'M' => 16,
            ]
        );
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
            'L' => '#,##0.00 [$₽-x-ru-RU]',
        ];
    }

    public function collection()
    {
        return $this->refunds;
    }
}
