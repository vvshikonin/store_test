<?php

namespace App\Casts;

use Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Преобразует `boolean` в `string`: `'Да'|'Нет'`.
 */
class TransactionableTypeName implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        switch ($value) {
            case 'App\\Models\\V1\\OrderProduct':
                return 'Заказ: ' . ($model->transactionable->order->number ?? 'товар удален');
            case 'App\\Models\\V1\\InvoiceProduct':
            case 'App\\Models\\V1\\Invoice':
                return 'Счёт: ' . ($model->transactionable->invoice->number ?? $model->transactionable->number ?? 'товар удалён');
            case 'App\\Models\\V1\\MoneyRefundable':
                switch ($model->transactionable->refundable_type) {
                    case 'App\\Models\\V1\\Invoice':
                        return 'Возврат ДС по счёту: ' . ($model->transactionable->invoice->number ?? $model->transactionable->number ?? 'товар удалён');
                    case 'App\\Models\\V1\\ProductRefund':
                        return 'Возврат ДС по возврату товара: №' . ($model->transactionable->productRefund->id ?? 'товар удалён');
                    case 'App\\Models\\V1\\Defect':
                        return 'Возврат ДС по браку: ' . ($model->transactionable->defect->id ?? 'товар удалён');
                }
                return 'Возврат ДС: ' . $model->transactionable->refundable_type;
            case 'App\\Models\\MoneyRefundIncome':
                return 'Возврат ДС: ' . $model->transactionable->money_refundable_id;
            case 'App\\Models\\V1\\InventoryProduct':
                return 'Инвентаризация №' . ($model->transactionable->inventory->id ?? 'товар удалён');
            // case 'App\\Models\\V1\\ContractorRefundProduct':
            //     return 'Возврат поставщику'  . ($model->transactionable->invoice->number ?? 'товар удалён');
            case 'App\\Models\\V1\\Product':
                return 'Корректировка в товаре';
            case 'App\\Models\\V1\\Stock':
                return 'Исходный остаток';
            case 'App\\Models\\V1\\DebtPayment':
                return 'Оплата за счёт долга';
            case 'App\\Models\\V1\\Expenses\\Expense':
                return 'Хоз. расход: ' . $model->transactionable->id;
            default:
                return $value;
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        throw new HttpException('Попытка использовать set метод в TransactionableTypeName');
    }
}
