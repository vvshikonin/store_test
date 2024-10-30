<?php

namespace App\Exports\MoneyRefunds;

use App\Traits\DateHandle;

use App\Exports\FromCollectionExport;
use App\Exports\Contracts\withColumnDataTyping;
use App\Exports\Contracts\withColumnHozirontalAlignment;

use App\Models\V1\ContractorRefund;
use App\Models\V1\Defect;
use App\Models\V1\Invoice;
use App\Models\V1\MoneyRefundable;
use App\Models\V1\ProductRefund;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MoneyRefundIncomesExport extends FromCollectionExport implements withColumnHozirontalAlignment, withColumnDataTyping
{
    use DateHandle;

    private function reasonHundler($data)
    {
        switch ($data->moneyRefund->refundable_type) {
            case Invoice::class:
                return "Счёт " . $data->moneyRefund->refundable->number;
            case MoneyRefundable::class:
                return $data->moneyRefund->reason;
            case ContractorRefund::class:
                return "Возврат поставщику №" . $data->moneyRefund->refundable->id;
            case ProductRefund::class:
                return "Возврат товара №" . $data->moneyRefund->refundable->id;
            case Defect::class:
                return "Брак №" . $data->moneyRefund->refundable->id;
        }
    }

    public function map($data): array
    {
        $deduction = floatval($data->moneyRefund->debt_sum) - floatval($data->moneyRefund->refund_sum_money);

        return [
            $data->moneyRefund->id,
            $this->reasonHundler($data),
            $data->moneyRefund->contractor?->name,
            $data->moneyRefund->legalEntity?->name,
            $data->moneyRefund->paymentMethod?->name,
            $data->moneyRefund->status,
            $data->moneyRefund->debt_sum,
            $data->sum,
            number_format($deduction, 2, ',', ''),
            $data->moneyRefund->comment,
            $this->formatIfSet($data->completed_at, 'd.m.Y'),
            $this->formatIfSet($data->created_at, 'd.m.Y'),
        ];
    }

    public function headings(): array
    {
        return [
            '№',
            'Причина',
            'Поставщики',
            'Юр. лицо',
            'Способ оплаты',
            'Статус',
            'Сумма',
            'По факту',
            'Вычета',
            'Комментарий',
            'Завершён',
            'Создания',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 25,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 50,
            'K' => 15,
            'L' => 15
        ];
    }

    public function columnHorizontalAlignment()
    {
        return [
            'G' => Alignment::HORIZONTAL_CENTER,
            'H' => Alignment::HORIZONTAL_CENTER,
            'I' => Alignment::HORIZONTAL_CENTER,
        ];
    }

    public function columnDataTyping()
    {
        return ['B' => DataType::TYPE_STRING];
    }
}
