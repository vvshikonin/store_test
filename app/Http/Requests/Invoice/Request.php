<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    /**
     * Возвращает правила валидации для основных данных.
     * @return string[]
     */
    protected function mainDataRules()
    {
        $rules = [
            'number' => 'required|string|max:255',
            'contractor_id' => 'required|integer|exists:contractors,id',
            'date' => 'required|date',
            'legal_entity_id' => 'required|integer|exists:legal_entities,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx',
            'comment' => 'nullable|string|max:500',
        ];

        return $rules;
    }

    /**
     * Возвращает правила валидации для данных об оплате.
     * @return string[]
     */
    protected function paymentRules()
    {
        $rules = [
            'payment_method_id' => 'nullable|integer|exists:payment_methods,id',
            'payment_status' => 'required|int|',
            'payment_confirm' => 'nullable|boolean',
            'payment_date' => 'nullable|date',
            'payment_order_date' => 'nullable|date',
            'receipt_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx',
        ];

        $isProductReceived = false;
        if (collect($this?->products)->sum('received'))
            $isProductReceived = true;

        if (collect($this?->new_products)->sum('received'))
            $isProductReceived = true;

        $validationConditions = $this->payment_status || $isProductReceived;
        if ($validationConditions)
            $rules['payment_method_id'] = 'required|integer|exists:payment_methods,id';

        return $rules;
    }

    /**
     * Возвращает правила валидации для данных о доставки.
     * @return string[]
     */
    protected function deliveryRules()
    {
        $rules = ['delivery_type' => 'nullable|integer'];
        return $rules;
    }

    /**
     * Возвращает правила валидации для новых товаров счёта.
     * @return string[]
     */
    protected function newProductsRules()
    {
        $rules = [
            'new_products' => 'array',
            'new_products.*.product_id' => 'required|integer|exists:products,id',
            'new_products.*.price' => 'required|numeric|min:0',
            'new_products.*.amount' => 'required|integer|min:1',
            'new_products.*.received' => 'required|integer|min:0',
            'new_products.*.refused' => 'required|integer|min:0',
            'new_products.*.planned_delivery_date_from' => 'nullable|date|required_with:new_products.*.planned_delivery_date_to',
            'new_products.*.planned_delivery_date_to' => 'nullable|date|required_with:new_products.*.planned_delivery_date_from',
        ];

        $productsCount = collect($this?->products)->count();
        if (!$productsCount)
            $rules['new_products'] = 'required|array';

        return $rules;
    }

    /**
     * Возвращает правила валидации для товаров счёта.
     * @return string[]
     */
    protected function productsRules()
    {
        $rules = [
            'products' => 'array',
            'products.*.id' => 'required|integer',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.amount' => 'required|integer|min:1',
            'products.*.received' => 'required|integer|min:0',
            'products.*.refused' => 'required|integer|min:0',
            'products.*.planned_delivery_date_from' => 'nullable|date',
            'products.*.planned_delivery_date_to' => 'nullable|date',
        ];

        return $rules;
    }


    /**
     * Возвращает правила валидации для удалённых товаров счёта.
     * @return string[]
     */
    protected function deletedProductsRules()
    {
        $rules = [
            'deleted_products' => 'array',
            'deleted_products.*' => 'exists:invoice_products,id',
        ];

        return $rules;
    }

    /**
     * Определяет название полей для вывода ошибок.
     * @return string[]
     */
    public function attributes()
    {
        return [
            'number' => '"Номер счёта"',
            'contractor_id' => '"Поставщик"',
            'date' => '"Дата счёта"',
            'legal_entity_id' => '"Юр.лицо"',
            'file' => '"Файл счета"',
            'comment' => '"Комментарий"',
            'payment_method_id' => '"Способ оплаты"',
            'payment_status' => '"Статус оплаты"',
            'payment_confirm' => '"Статус подтверждение оплаты директором"',
            'payment_date' => '"Дата оплаты"',
            'receipt_file' => '"Файл чека"',
            'delivery_type' => '"Способ доставки"',
            
            'new_products' => '"Товары"',
            'new_products.*.planned_delivery_date_to' => '"Планируемая дата доставки"',
            'new_products.*.planned_delivery_date_from' => '"Планируемая дата доставки"',
            'new_products.*.price' => '"Цена"',
            'new_products.*.amount' => '"Количество"',
            'new_products.*.received' => '"Оприходовано"',
            'new_products.*.refused' => '"Отказ"',

            'products' => '"Товары"',
            'products.*.planned_delivery_date_to' => '"Планируемая дата доставки"',
            'products.*.planned_delivery_date_from' => '"Планируемая дата доставки"',
            'products.*.price' => '"Цена"',
            'products.*.amount' => '"Количество"',
            'products.*.received' => '"Оприходовано"',
            'products.*.refused' => '"Отказ"',
        ];
    }

    /**
     * Определяет кастомные сообщения об ошибки.
     * @return string[]
     */
    public function messages()
    {
        return [
            'new_products.required' => 'В счёте должен быть хотя бы один товар!',
        ];
    }
}
