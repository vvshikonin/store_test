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

use Illuminate\Support\Facades\Log;

class MoneyRefundsExport extends FromCollectionExport implements withColumnHozirontalAlignment, withColumnDataTyping
{
    use DateHandle;

    private function reasonHundler($data)
    {
        switch ($data->refundable_type) {
            case Invoice::class:
                return "Счёт " . $data->refundable->number;
            case MoneyRefundable::class:
                return $data->reason;
            case ContractorRefund::class:
                return "Возврат поставщику №" . $data->refundable->id;
            case ProductRefund::class:
                return "Возврат товара №" . $data->refundable_id;
            case Defect::class:
                return "Брак №" . $data->refundable->id;
        }
    }

    public function map($data): array
    {
        // Преобразуем строковые значения в float с учетом разделителя
        $debtSum = (float)str_replace(',', '.', $data->debt_sum);
        $refundSumMoney = (float)str_replace(',', '.', $data->refund_sum_money);
        $refundSumProducts = (float)str_replace(',', '.', $data->refund_sum_products);

        // Добавим более детальное логирование для отладки
        Log::info('Исходные значения:', [
            'debt_sum_raw' => $data->debt_sum,
            'debt_sum_converted' => $debtSum,
            'refund_sum_money_raw' => $data->refund_sum_money,
            'refund_sum_money_converted' => $refundSumMoney,
            'refund_sum_products_raw' => $data->refund_sum_products,
            'refund_sum_products_converted' => $refundSumProducts
        ]);

        // Вычисляем задолженность для вычета с округлением до 2 знаков
        $deduction = round($debtSum - ($refundSumMoney + $refundSumProducts), 2);

        return [
            $data->id,
            $this->reasonHundler($data),
            $data->contractor?->name,
            $data->legalEntity?->name,
            $data->paymentMethod?->name,
            $data->status,
            $data->debt_sum,
            $data->refund_sum_money,
            $deduction ?? 0,
            $data->comment,
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
            'Задолж. для вычета',
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
            'I' => 20,
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
