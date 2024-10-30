<?php

namespace App\Services\History;

use App\Models\V1\InvoiceProduct;

/**
 * Class InvoicePaymentHistoryService
 * 
 * Сервис для управления историей отказа в счетов.
 */
class InvoiceProductHistoryService
{
    /**
     * Обрабатывает отказ от товаров в счетах и предоставляет точку входа в сервис истории отказа от товаров.
     *
     * @param InvoiceProduct $invoiceProduct Объект счета, который был обновлен.
     * @return void
     */
    public function handleProductRefused(InvoiceProduct $invoiceProduct)
    {
        if (!$invoiceProduct->isDirty('refused')) {
            \Log::info('Нет изменений в поле "refused" для InvoiceProduct ID: ' . $invoiceProduct->id);
            return;
        }

        // Получаем исходное значение 'refused'
        $originalRefused = $invoiceProduct->getOriginal('refused');
        $currentRefused = $invoiceProduct->refused;

        // Вычисляем разницу
        $difference = $currentRefused - $originalRefused;

        // Проверяем, что разница не равна нулю
        if ($difference != 0) {
            $this->createInvoiceProductHistoryRecord($invoiceProduct, $difference);
        }
    }

    /**
     * Создаёт запись в истории отказов от товаров.
     *
     * @param InvoiceProduct $invoiceProduct Объект счета, для которого создаётся запись.
     * @return void
     */
    private function createInvoiceProductHistoryRecord(InvoiceProduct $invoiceProduct, int $difference)
    {
        $invoiceProduct->refusesHistory()->create([
            'invoice_product_id' => $invoiceProduct->id,
            'amount' => $difference,
            'user_id' => auth()->id(),
        ]);
    }
}
