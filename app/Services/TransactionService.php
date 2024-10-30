<?php

namespace App\Services;

use App\Models\V1\Stock;
use App\Models\V1\Transactionable;
use App\Models\V1\InvoiceProduct;
use App\Models\V1\OrderProduct;
use App\Services\Transactions\AbstractTransactionService;
use App\Mail\LowStockNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\V1\Product;

class TransactionService extends AbstractTransactionService
{
    /**
     * Производит транзакцию на поступление нового товара.
     *
     * @param mixed $relatedModel Eloquent модель, которая будет связана с транзакцией.
     * @param int $amount Если 0, то транзакция не будет произведена.
     * @param int $productId
     * @param int $contractorId
     * @param float $price
     * @return bool Возвращает true если транзакция произведена, false если транзакция не была произведена.
     */
    public function makeIncomingTransaction($relatedModel, $amount, $productId, $contractorId, $price)
    {
        if ($relatedModel instanceof InvoiceProduct)
        {
            $stock = $this->createOrUpdatePendingStocks([
                'product_id' => $productId,
                'contractor_id' => $contractorId,
                'price' => $price,
            ], $relatedModel);
        } else {
            if ($amount == 0)
                return false;

            $stock = Stock::firstOrCreate([
                'product_id' => $productId,
                'contractor_id' => $contractorId,
                'price' => $price,
            ]);
        }

        if ($amount == 0)
            return false;

        $newTransaction = $this->createTransaction($stock, self::IN, $amount, $relatedModel);

        if ($relatedModel instanceof InvoiceProduct) {
            $stock->last_receive_date = $newTransaction->created_at; // Устанавливаем текущую дату
            $stock->save(); // Сохраняем изменения
        }

        $this->updateStockAmount($stock);
        return true;
    }

    /**
     * Производит транзакцию на списание товара.
     *
     * @param mixed $relatedModel Eloquent модель.
     * @param int $amount Если 0, то транзакция не будет произведена.
     * @param int $productId
     * @param int|null $contractorId Если null, то будет произведено списание без учёта поставщика.
     * @return bool Возвращает true если транзакция произведена, false если транзакция не была произведена.
     */
    public function makeOutcomingTransaction($relatedModel, $amount, $productId, $contractorId = null, $price = null)
    {
        if ($amount == 0)
            return false;

        // $isStockAboveHalfBefore = $this->isFreeStockValid($productId);

        $product = Product::findOrFail($productId);
        $isStockAboveHalfBefore = $product->is_sale;

        $stocks = Stock::where('product_id', $productId)->where('amount', '>', '0');

        if ($price !== null)
            $stocks = $stocks->where('price', $price);
        else
            $stocks = $stocks->where('price', '>', '0');

        if ($contractorId !== null)
            $stocks = $stocks->where('contractor_id', $contractorId);

        $stocks = $stocks->orderBy('price', 'asc');

        $stocksAmountSum = $stocks->sum('amount');
        if ($stocksAmountSum < $amount)
            throw new HttpException(422, "Невозможно пересчитать остатки!");

        $remainingAmount = $amount;
        foreach ($stocks->get() as $stock) {
            if ($stock->amount < $remainingAmount) {
                $remainingAmount -= $stock->amount;
                $this->createTransaction($stock, self::OUT, $stock->amount, $relatedModel);
                $this->updateStockAmount($stock);
                $this->updateStockSaled($stock, $amount);
            } else if ($stock->amount >= $remainingAmount) {
                $this->createTransaction($stock, self::OUT, $remainingAmount, $relatedModel);
                $this->updateStockAmount($stock);
                $this->updateStockSaled($stock, $amount);
                $remainingAmount = 0;
                break;
            }
        }

        if ($remainingAmount > 0) {
            $this->rollbackDBTransactions();
            return false;
        }

        $isFreeStockValid = $this->isFreeStockValid($productId);

        Log::debug($product->main_sku . ' ' . $isStockAboveHalfBefore . ' -> ' . $isFreeStockValid);
        if ($isStockAboveHalfBefore && !$isFreeStockValid) {
            Mail::to('taskstoit@yandex.ru')->send(new LowStockNotification($productId));
        }

        return true;
    }

    /**
     * Меняет `Stock` по переданной связанной модели у транзакций.
     *
     * @param mixed $relatedModel Eloquent модель связанная с транзакциями.
     * @param int $newProductId
     * @param int $newContractorId.
     * @param float $newPrice.
     * @param self::IN|self::OUT|null $type Допустимые значения: 'In', 'Out', null.
     */
    public function makeReplaceStock($relatedModel, $newProductId, $newContractorId, $newPrice, $type = null)
    {
        if ($relatedModel instanceof InvoiceProduct)
        {
            $currentStock = $this->createOrUpdatePendingStocks([
                'product_id' => $newProductId,
                'contractor_id' => $newContractorId,
                'price' => $newPrice,
            ], $relatedModel);
        } else {
            $currentStock = Stock::firstOrCreate([
                'product_id' => $newProductId,
                'contractor_id' => $newContractorId,
                'price' => $newPrice,
            ]);
        }

        // if ($type === null || $type === self::OUT) {
        //     $outTransactions = Transactionable::where('transactionable_type', get_class($relatedModel))
        //         ->where('transactionable_id', $relatedModel->id)->where('type', self::OUT)->get()->load(['stock']);
        // }

        // if ($type === null || $type === self::IN) {
        //     $inTransactions = Transactionable::where('transactionable_type', get_class($relatedModel))
        //         ->where('transactionable_id', $relatedModel->id)->where('type', self::IN)->get()->load(['stock']);
        // }

        // foreach ($outTransactions as $transaction) {
        //     $oldStock = $transaction->stock;

        //     $transaction->stock()->associate($currentStock);
        //     $transaction->save();

        //     $this->updateStockAmount($oldStock);
        // }

        // foreach ($inTransactions as $transaction) {
        //     $oldStock = $transaction->stock;

        //     $transaction->stock()->associate($currentStock);
        //     $transaction->save();

        //     $this->updateStockAmount($oldStock);
        // }

        $this->associateStockTransactions($currentStock, $relatedModel, $type);

        $this->updateStockAmount($currentStock);

        return true;
    }


    /**
     * Производит отмену транзакций по переданной связанной модели.
     *
     * @param mixed $relatedModel Eloquent модель связанная с транзакциями.
     * @param self::IN|self::OUT|null $type Допустимые значения: 'In', 'Out', null.
     * Если null, то отмена транзакций будет произведена без учёта типа.
     * @return bool Возвращает true если транзакция произведена, false если транзакция не была произведена.
     */
    public function makeRollbackTransactions($relatedModel, $type = null)
    {
        $transactions = Transactionable::where('transactionable_type', get_class($relatedModel))
            ->where('transactionable_id', $relatedModel->id);

        if ($type !== null)
            $transactions = $transactions->where('type', $type);

        $stocks = Stock::whereIn('id', $transactions->select('stock_id'))->get();

        // Получаем сумму по транзакциям для вычитания из saled остатка
        if ($type == 'Out')
            $amount = $transactions->sum('amount');

        $transactions->delete();

        foreach ($stocks as $stock) {
            $this->updateStockAmount($stock);

            // Вычитаем сумму по транзакциям из saled остатка
            if ($type == 'Out')
                $this->updateStockSaled($stock, -$amount);
        }

        return true;
    }

    /**
     * Создаёт запись о новой транзакции в БД.
     *
     * @param App\Models\V1\Stock $stock
     * @param string|null $type Допустимые значения: TransactionService::IN, TransactionService::OUT.
     * @param int $amount
     * @param mixed $relatedModel Eloquent модель, которая будет связана с транзакцией.
     */
    private function createTransaction(&$stock, $type, $amount, $relatedModel)
    {
        if ($amount == 0)
            return false;

        $transaction = new Transactionable();
        $transaction->transactionable_id = $relatedModel->id;
        $transaction->transactionable_type = get_class($relatedModel);
        $transaction->stock_id = $stock->id;
        $transaction->type = $type;
        $transaction->amount = $amount;
        $transaction->user_id = auth()->user()->id;
        $transaction->save();

        return $transaction;
    }

    /**
     * Производит перерасчёт переданного остатка.
     *
     * @param App\Models\V1\Stock $stock
     */
    private function updateStockAmount(&$stock)
    {
        $incomingTransactions = Transactionable::where('stock_id', $stock->id)->where('type', self::IN)->sum('amount');
        $outcomingTransactions = Transactionable::where('stock_id', $stock->id)->where('type', self::OUT)->sum('amount');

        $stock->amount = $incomingTransactions - $outcomingTransactions;

        if ($stock->amount < 0) {
            throw new HttpException(422, "Невозможно пересчитать остатки!");
        } else {
            $stock->save();
        }

        $productAmount = Stock::where('product_id', $stock->product_id)
            ->where('amount', '>', 0)
            ->sum('amount');

        if ($productAmount < 1) {
            $stock->product->is_sale = 0;
            $stock->product->save();
        }
    }

    /**
     * Обновляет "Продано" переданного остатка.
     *
     * @param App\Models\V1\Stock $stock
     * @param $amount
     */
    private function updateStockSaled(&$stock, $amount)
    {
        $stock->saled += $amount;
        $stock->last_saled_date = now()->format('Y-m-d');
        $stock->save();
    }

    /**
     * Проверяет, является ли товар акционным (поле is_sale установлено в true)
     *
     * @param int $productId
     *
     * @return bool возвращает True, если товар акционный, и False, если не является акционным
     */
    protected function isFreeStockValid($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return false; // Если товар не найден, возвращаем false
        }

        // $salePrice = $product->salePrice;
        // if ($salePrice < 10)
        // {
        //     $product->is_sale = false;
        //     $product->save();

        //     return false;
        // }

        $totalStock = $product->stocks()->sum('amount');
        $reservedAmount = $product->orderProducts()->whereHas('order', function ($query) {
            $query->where('state', 'reserved');
        })->sum('amount');

        Log::debug('totalStock: ' . $totalStock);
        Log::debug('reservedAmount: ' . $reservedAmount);

        $freeStock = $totalStock - $reservedAmount;
        Log::debug('freeStock: ' . $freeStock);

        $isSaleConditionMet = ($freeStock > (0.5 * $totalStock)) && ($product->sale_type !== 'nonsale');
        Log::debug('isSaleConditionMet: ' . $isSaleConditionMet);

        // Обновляем статус акции товара
        if ($isSaleConditionMet) {
            $product->is_sale = true;
        } else if ($product->is_sale) {
            $this->checkOldSale($product);
            $product->is_sale = false;
        }

        $product->save();

        Log::debug('is_sale: ' . $product->is_sale);
        return $product->is_sale;
    }

    /**
     * Связывает транзакции с переданным остатком.
     *
     * @param App\Models\V1\Stock $stock
     * @param mixed $relatedModel Eloquent модель, которая будет связана с транзакциями.
     * @param self::IN|self::OUT|null $type Допустимые значения: TransactionService::IN, TransactionService::OUT.
     */
    private function associateStockTransactions(&$stock, $relatedModel, $type)
    {
        $transactions = Transactionable::where('transactionable_type', get_class($relatedModel))
            ->where('transactionable_id', $relatedModel->id);

        if ($type !== null)
            $transactions = $transactions->where('type', $type);

        $transactions = $transactions->get()->load(['stock']);

        foreach ($transactions as $transaction) {
            $oldStock = $transaction->stock;

            $transaction->stock()->associate($stock);
            $transaction->save();

            $this->updateStockAmount($oldStock);
        }
    }

    /**
     * Создаёт новый или обновляет существующий `Stock` для товара в счёте.
     *
     * @param array $data
     * @param \App\Models\V1\InvoiceProduct $invoiceProduct
     * @return \App\Models\V1\Stock
     * @throws \Exception
     */
    private function createOrUpdatePendingStocks(array $data, InvoiceProduct $invoiceProduct): Stock
    {
        if ($invoiceProduct->pending_stock_id) {
            // Получаем существующий Stock
            $stock = Stock::find($invoiceProduct->pending_stock_id);

            if ($stock) {
                // Обновляем поля Stock
                $stock->update([
                    'product_id' => $data['product_id'],
                    'contractor_id' => $data['contractor_id'],
                    'price' => $data['price'],
                ]);

                Log::info("Stock ID: {$stock->id} обновлён для InvoiceProduct ID: {$invoiceProduct->id}");

                return $stock;
            } else {
                // Если Stock не найден, очищаем pending_stock_id и создаём новый Stock
                Log::warning("Stock с ID: {$invoiceProduct->pending_stock_id} не найден. Создаётся новый Stock.");
                $invoiceProduct->pending_stock_id = null;
                $invoiceProduct->save();
            }
        }

        // Создаём новый Stock
        $newStock = Stock::firstOrCreate([
            'product_id' => $data['product_id'],
            'contractor_id' => $data['contractor_id'],
            'price' => $data['price'],
        ]);

        // Связываем новый Stock с InvoiceProduct
        $invoiceProduct->pending_stock_id = $newStock->id;
        $invoiceProduct->save();

        Log::info("Новый Stock ID: {$newStock->id} создан и связан с InvoiceProduct ID: {$invoiceProduct->id}");

        return $newStock;
    }

    /**
     * Проверяет, является ли товар акционным из-за трехнедельного периода последней продажи.
     *
     * @param \App\Models\V1\Product $product
     *
     * @return void
     */
    private function checkOldSale(Product $product)
    {
        \Log::info('Проверка товара на распродажу: ' . $product->id . ' с SKU: ' . $product->main_sku);
        $latestStock = $product->stocks()
                               ->orderBy('last_saled_date', 'desc')
                               ->first();

        \Log::info('Последняя дата продажи: ' . $latestStock->last_saled_date);

        // Проверяем, существует ли Stock и удовлетворяет ли он условию
        if ($latestStock && $latestStock->last_saled_date <= now()->subDays(21))
        {
            \Log::info('Условие выполнено: ' . $latestStock->last_saled_date . ' <= ' . now()->subDays(21)->format('Y-m-d'));
            $product->sale_type = 'auto';
            $product->sale_multiplier = null;
            $product->save();
        }
    }
}
