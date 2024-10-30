<?php

namespace App\Services\Entities;

use App\Models\V1\Invoice;
use App\Models\V1\InvoiceProduct;
use App\Events\InvoicePaymentStatusSet;
use App\Events\InvoiceProductRefused;
use App\Services\Entities\MoneyRefundService;
use App\Services\Entities\DebtPaymentService;
use App\Services\Transactions\Cash\InvoiceCashTransactions;
use App\Services\Transactions\Products\InvoiceProductTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvoiceService
{
    protected $productTransaction;
    protected $cashTransaction;
    protected $moneyRefundService;
    protected $debtPaymentService;

    public function __construct(
        InvoiceProductTransactions $productTransaction,
        InvoiceCashTransactions $cashTransaction,
        MoneyRefundService $moneyRefundService,
        DebtPaymentService $debtPaymentService
    ) {
        $this->productTransaction = $productTransaction;
        $this->cashTransaction = $cashTransaction;
        $this->moneyRefundService = $moneyRefundService;
        $this->debtPaymentService = $debtPaymentService;
    }

    /**
     * Обрабатывает логику создания нового счёта.
     *
     * @param array $data
     * @return \App\Models\V1\Invoice
     */
    public function create($data)
    {
        $invoice = new Invoice;
        return DB::transaction(function () use (&$invoice, $data) {
            $invoice->fill($data);

            if (isset($data['is_edo'])) {
                $invoice->is_edo = $data['is_edo'] === 'true' ? true : false;
            }

            if (isset($data['payment_status']))
                $this->setPaymentStatus($invoice, $data['payment_status']);

            if (isset($data['payment_date']))
                $this->setPaymentDate($invoice, $data['payment_date']);

            if (isset($data['payment_order_date']))
                $invoice->payment_order_date = $data['payment_order_date'];

            $this->setPaymentConfirm($invoice, $data['payment_confirm']);
            $invoice->save();

            $this->createInvoiceProducts($invoice, $data['new_products']);
            $this->setInvoiceStatus($invoice);
            $invoice->save();

            $this->dispatchPaymentStatusSetEvent($invoice);

            $this->handleCashFlow($invoice);
            $this->handleMoneyRefund($invoice);
            $this->handleDebtPayment($invoice);

            return $invoice;
        });
    }

    /**
     * Обрабатывает логику обновления счёта.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param array $data
     * @return \App\Models\V1\Invoice
     */
    public function update(&$invoice, $data)
    {
        return DB::transaction(function () use (&$invoice, $data) {
            $invoice->saveOriginalModel();

            if (isset($data['comment']))
                $invoice->comment = $data['comment'];

            if (Gate::allows('update', Invoice::class)) {
                $invoice->fill($data);
                if (isset($data['is_edo'])) {
                    $invoice->is_edo = $data['is_edo'] === 'true' ? true : false;
                }
                if (isset($data['is_edo_all']) && $data['is_edo_all']) {
                    $invoice->is_edo = true;
                }
            }

            if (isset($data['payment_status']))
                $this->setPaymentStatus($invoice, $data['payment_status']);

            if (isset($data['payment_date']))
                $this->setPaymentDate($invoice, $data['payment_date']);

            if (isset($data['payment_order_date']))
                $invoice->payment_order_date = $data['payment_order_date'];

            if (isset($data['payment_confirm']))
                $this->setPaymentConfirm($invoice, $data['payment_confirm']);

            $allowProductUpdate = Gate::any(['position-update', 'received-update', 'refused-update'], Invoice::class);
            if (isset($data['products']) && $allowProductUpdate)
                $this->updateInvoiceProducts($invoice, $data['products']);

            if (isset($data['new_products']) && Gate::allows('position-create', Invoice::class))
                $this->createInvoiceProducts($invoice, $data['new_products']);

            if (isset($data['deleted_products']) && Gate::allows('position-delete', Invoice::class))
                $this->deleteInvoiceProducts($invoice, $data['deleted_products']);

            if (isset($data['receive_all']) && $data['receive_all'])
                $this->receiveAll($invoice);

            $this->setInvoiceStatus($invoice);
            $invoice->save();

            $this->dispatchPaymentStatusSetEvent($invoice);

            $this->handleCashFlow($invoice);
            $this->handleMoneyRefund($invoice);
            $this->handleDebtPayment($invoice);

            return $invoice;
        });
    }

    /**
     * Обрабатывает логику удаления счёта.
     *
     * @param \App\Models\V1\Invoice $invoice
     */
    public function delete($invoice)
    {
        DB::transaction(function () use (&$invoice) {
            $this->moneyRefundService->delete($invoice);
            $this->cashTransaction->rollbackCashFlow($invoice);
            $this->deleteInvoiceProducts($invoice, $invoice->invoiceProducts->pluck('id')->all());
            $invoice->delete();
        });
    }

    /**
     * Устанавливает значение полю `payment_confirm`.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param mixed $newValue
     */
    private function setPaymentConfirm(&$invoice, $newValue)
    {
        if (Gate::allows('payment-confirm-update', Invoice::class))
            $invoice->payment_confirm = $newValue;
    }

    /**
     * Устанавливает значение полю `payment_confirm`.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param mixed $newValue
     */
    private function setPaymentStatus(&$invoice, $newValue)
    {
        if (Gate::allows('update', Invoice::class)) {
            $invoice->payment_status = $newValue;
            $invoice->payment_date = $newValue ? now() : null;
        }
    }

    /**
     * Устанавливает значение полю `payment_confirm`.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param mixed $newValue
     */
    private function setPaymentDate(&$invoice, $newValue)
    {
        if (Gate::allows('update', Invoice::class))
            $invoice->payment_date = $newValue;
    }

    /**
     * Устанавливает статус счёта и производит дополнительную обработку на основе статуса.
     *
     * @param \App\Models\V1\Invoice $invoice
     */
    private function setInvoiceStatus(&$invoice)
    {
        $invoice->load('invoiceProducts');
        if ($invoice->expectedAmount == $invoice->productsAmount)
            $invoice->status = 0; // Ожидается
        else if ($invoice->processedAmount < $invoice->productsAmount)
            $invoice->status = 1; // Частично оприходован
        else if ($invoice->refusedAmount == $invoice->productsAmount)
            $invoice->status = 3; // Отказ
        else if ($invoice->processedAmount == $invoice->productsAmount)
            $invoice->status = 2; // Оприходован

        $isPaidСondition = $invoice->status == 2 && !!$invoice->paymentMethod && $invoice->paymentMethod->type == 0;
        $isPaidСonditionChanged = $invoice->wasRecentlyCreated || $invoice->isDirty('status') ||
            ($invoice?->paymentMethod && $invoice->originalModel?->paymentMethod?->type != $invoice?->paymentMethod?->type);

        if ($isPaidСondition && $isPaidСonditionChanged) {
            $invoice->payment_status = 1;
            $invoice->payment_date = now();
        } else if ($invoice?->paymentMethod && $invoice?->paymentMethod?->type == 0 && $isPaidСonditionChanged) {
            $invoice->payment_status = 0;
            $invoice->payment_date = null;
        }

        if ($invoice->isDirty('status'))
            $invoice->status_set_at = now();
    }

    /**
     * Создаёт новые `InvoiceProduct` связанные с переданным `Invoice`.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param array $newData
     */
    private function createInvoiceProducts(&$invoice, $newData)
    {
        $newInvoiceProducts = [];
        foreach ($newData as $data) {

            $newInvoiceProduct = new InvoiceProduct;
            $newInvoiceProduct->fill($data);

            $this->setReceived($newInvoiceProduct, $data['received']);
            $this->setRefused($newInvoiceProduct, $data['refused']);

            $newInvoiceProducts[] = $newInvoiceProduct;
        }

        $newInvoiceProducts = $invoice->invoiceProducts()->saveMany($newInvoiceProducts);
        foreach ($newInvoiceProducts as $invoiceProduct)
            $this->handleProductIncoming($invoice, $invoiceProduct);
    }

    /**
     * Обновляет `InvoiceProduct` связанные с переданным `Invoice`.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param array $newData
     */
    private function updateInvoiceProducts(&$invoice, $newData)
    {
        $invoiceProducts = [];
        foreach ($newData as $data) {
            $invoiceProduct = $invoice->invoiceProducts->find($data['id']);
            $invoiceProduct->fill($data);

            $this->setReceived($invoiceProduct, $data['received']);
            $this->setRefused($invoiceProduct, $data['refused']);

            $invoiceProducts[] = $invoiceProduct;
            $this->handleProductIncoming($invoice, $invoiceProduct);
        }

        $invoice->invoiceProducts()->saveMany($invoiceProducts);
    }

    /**
     * Удаляет `InvoiceProduct` связанные с переданным `Invoice`.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param array $deletedInvoiceProductsIDs массив `id` моделей `InvoiceProduct`.
     */
    private function deleteInvoiceProducts(&$invoice, $deletedInvoiceProductsIDs)
    {
        $invoiceProducts = $invoice->invoiceProducts->only($deletedInvoiceProductsIDs);

        foreach ($invoiceProducts as $invoiceProduct)
            $this->productTransaction->rollbackProductFlow($invoiceProduct);

        $invoice->invoiceProducts()->whereIn('id', $deletedInvoiceProductsIDs)->delete();
    }

    /**
     * Обрабатывает поступление товара.
     *
     * @param \App\Models\V1\Invoice $invoice
     * @param \App\Models\V1\Invoiceproduct $invoiceProduct.
     */
    private function handleProductIncoming($invoice, &$invoiceProduct)
    {
        $isProductChanged = $invoice->isDirty('contractor_id') || $invoiceProduct->price != $invoiceProduct->getOriginal('price')
            || $invoiceProduct->isDirty('product_id');

        if ($invoiceProduct->wasRecentlyCreated) {
            $this->productTransaction->makeProductIncomingFlow($invoice, $invoiceProduct, $invoiceProduct->received);
        } else if ($invoiceProduct->getOriginal('received') < $invoiceProduct->received) {
            $receivedDiff = $invoiceProduct->received - $invoiceProduct->getOriginal('received');
            $this->productTransaction->makeProductIncomingFlow($invoice, $invoiceProduct, $receivedDiff);
        } else if ($invoiceProduct->getOriginal('received') > $invoiceProduct->received) {
            $receivedDiff = $invoiceProduct->getOriginal('received') - $invoiceProduct->received;
            $this->productTransaction->makeProductOutcomingFlow($invoice, $invoiceProduct, $receivedDiff);
        } else if ($isProductChanged) {
            $this->productTransaction->replaceProductFlow($invoice, $invoiceProduct);
        }
    }

    /**
     * Обрабатывает движение ДС.
     *
     * @param \App\Models\V1\Invoice $invoice
     */
    private function handleCashFlow(&$invoice)
    {
        /**
         * @var bool Безналичная оплата.
         */
        $isPaymentCashless = !!$invoice->paymentMethod && !!$invoice->paymentMethod->type;

        /**
         * @var bool Счёт оплачен.
         */
        $isPaymentStatusPaid = $invoice->payment_status == 1;

        if ($isPaymentStatusPaid) {
            if ($invoice->wasChanged('payment_status') || $invoice->wasRecentlyCreated) {
                $this->cashTransaction->makeCashFlow(
                    $invoice,
                    $isPaymentCashless
                        ? $invoice->invoiceSum - $invoice->debt_payment
                        : (($invoice->receivedSum - $invoice->debt_payment) < 0 ? 0 : $invoice->receivedSum - $invoice->debt_payment),
                    $invoice->payment_date
                );
            } elseif (
                $invoice->wasChanged('payment_method_id') ||
                $invoice->wasChanged('debt_payment') ||
                $invoice->wasChanged('payment_date') ||
                $isPaymentCashless && $invoice->invoiceSum != $invoice->originalModel->invoiceSum ||
                !$isPaymentCashless && $invoice->receivedSum < $invoice->originalModel->receivedSum
            ) {
                $this->cashTransaction->replaceCashFlow(
                    $invoice,
                    $isPaymentCashless
                        ? $invoice->invoiceSum - $invoice->debt_payment
                        : (($invoice->receivedSum - $invoice->debt_payment) < 0 ? 0 : $invoice->receivedSum - $invoice->debt_payment),
                    $invoice->payment_date
                );
            }
        } else {
            $this->cashTransaction->rollbackCashFlow($invoice);
        }
    }

    /**
     * Обрабатывает возврат ДС.
     *
     * @param \App\Models\V1\Invoice $invoice
     */
    private function handleMoneyRefund(&$invoice)
    {
        $isRefundable = $invoice->refusedAmount > 0 && $invoice->payment_status;
        $refundDataChanged =
            $invoice->wasChanged('payment_method_id') ||
            $invoice->wasChanged('contractor_id') ||
            (!$invoice->wasRecentlyCreated && $invoice->refusedAmount != $invoice->originalModel->refusedAmount);

        if ($isRefundable) {
            if ($invoice->originalModel->refusedAmount == 0 || $invoice->wasChanged('payment_status')) {
                $this->moneyRefundService->create(
                    $invoice,
                    $invoice->refusedSum,
                    $invoice->contractor_id,
                    $invoice->legal_entity_id,
                    $invoice->payment_method_id
                );
            } else if ($refundDataChanged) {
                $refund = $invoice->moneyRefundable;
                $data = [
                    'debt_sum' => $invoice->refusedSum,
                    'contractor_id' => $invoice->contractor_id,
                    'legal_entity_id' => $invoice->legal_entity_id,
                    'payment_method_id' => $invoice->payment_method_id,
                    // 'new_incomes' => $invoice->
                ];

                $this->moneyRefundService->update($refund, $data);
            }
        } else $this->moneyRefundService->delete($invoice);
    }

    /**
     * Устанавливает значение полю `received`.
     *
     * @param \App\Models\V1\InvoiceProduct $invoiceProduct
     * @param mixed $newValue
     */
    private function setReceived(&$invoiceProduct, $newValue)
    {
        if (Gate::allows('received-update', Invoice::class)) {
            $invoiceProduct->received = $newValue;

            if ($invoiceProduct->received == 0)
                $invoiceProduct->received_at = null;
            elseif ($invoiceProduct->received > $invoiceProduct->getOriginal('received'))
                $invoiceProduct->received_at = now();
        }
    }

    /**
     * Устанавливает значение полю `refused`.
     *
     * @param \App\Models\V1\InvoiceProduct $invoiceProduct
     * @param mixed $newValue
     */
    private function setRefused(&$invoiceProduct, $newValue)
    {
        if (Gate::allows('refused-update', Invoice::class)) {
            $invoiceProduct->refused = $newValue;
            event(new InvoiceProductRefused($invoiceProduct));
        }
    }

    /**
     * Оприходует все товары в счёте с учётом отказов.
     *
     * @param \App\Models\V1\Invoice $invoice
     */
    private function receiveAll(&$invoice)
    {
        if (Gate::denies('received-update', Invoice::class))
            return;

        $updatedProducts = [];
        foreach ($invoice->invoiceProducts as $invoiceProduct) {
            $invoiceProduct->received = $invoiceProduct->amount - $invoiceProduct->refused;

            if (!$invoiceProduct->received_at)
                $invoiceProduct->received_at = now();

            $this->handleProductIncoming($invoice, $invoiceProduct);
            $updatedProducts[] = $invoiceProduct;
        }

        $invoice->invoiceProducts()->saveMany($updatedProducts);
    }

    /**
     * Обрабатывает оплату долгом поставщика.
     *
     * @param \App\Models\V1\Invoice $invoice
     */
    private function handleDebtPayment(&$invoice)
    {
        if ($invoice?->paymentMethod?->type === 0 && $invoice->debt_payment > $invoice->receivedSum) {
            throw new HttpException(422, 'Сумма оплаты долгом не может быть больше чем сумма оприходованных товаров, при оплате наличными.');
        }

        if ($invoice->wasChanged('debt_payment') || $invoice->wasRecentlyCreated) {
            $this->debtPaymentService->handlePayment($invoice);
        }
    }

    private function dispatchPaymentStatusSetEvent(&$invoice)
    {
        event(new InvoicePaymentStatusSet($invoice));
    }
}
