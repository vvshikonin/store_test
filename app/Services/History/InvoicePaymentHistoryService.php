<?php

namespace App\Services\History;

use App\Models\V1\Invoice;

/**
 * Class InvoicePaymentHistoryService
 * 
 * Сервис для управления историей оплаты счетов.
 */
class InvoicePaymentHistoryService
{
    /**
     * Обрабатывает обновление счета и предоставляет точку входа в сервис истории оплаты счетов.
     *
     * @param Invoice $invoice Объект счета, который был обновлен.
     * @return void
     */
    public function handleParametersSet(Invoice $invoice)
    {
        $lastHistory = $invoice->paymentHistory()->latest()->first();

        if (
            !$lastHistory ||
            $invoice->wasChanged('payment_status') ||
            $invoice->wasChanged('payment_method_id') ||
            $invoice->wasChanged('legal_entity_id') ||
            $invoice->wasChanged('payment_date')
        ) {
            $this->createInvoicePaymentHistoryRecord(
                $invoice,
                $invoice->payment_status,
                $invoice->payment_method_id,
                $invoice->legal_entity_id,
                $invoice->payment_date
            );
        }
    }

    /**
     * Создаёт запись в истории оплаты счёта.
     *
     * @param Invoice $invoice Объект счета, для которого создаётся запись.
     * @param string $status Статус оплаты.
     * @param int $paymentMethodId ID метода оплаты.
     * @param int $legalEntityId ID юридического лица.
     * @param string|null $paymentDate Дата оплаты.
     * @return void
     */
    private function createInvoicePaymentHistoryRecord(
        Invoice $invoice,
        $status,
        $paymentMethodId,
        $legalEntityId,
        $paymentDate
    ) {
        $invoice->paymentHistory()->create([
            'status' => $status,
            'payment_method_id' => $paymentMethodId,
            'legal_entity_id' => $legalEntityId,
            'payment_date' => $status === 0 ? null : $paymentDate,
            'user_id' => auth()->user()->id,
        ]);
    }
}
