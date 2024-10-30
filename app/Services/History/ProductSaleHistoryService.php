<?php

namespace App\Services\History;

use App\Models\V1\Product;
use App\Models\V1\ProductSaleHistory;

/**
 * Class ProductSaleHistoryService
 * 
 * Сервис для управления историей распродаж товаров.
 */
class ProductSaleHistoryService
{
    /**
     * Обрабатывает обновление товара и предоставляет точку входа в сервис истории распродаж.
     *
     * @param Product $product Объект товара, который был обновлен.
     * @return void
     */
    public function handleProductUpdate(Product $product)
    {
        $this->createOrUpdateSaleHistory($product);
    }

    /**
     * Создает новую запись или обновляет текущую запись в истории распродаж.
     *
     * @param Product $product Объект товара, для которого создается или обновляется запись истории.
     * @return void
     */
    protected function createOrUpdateSaleHistory(Product $product)
    {
        $lastSaleHistoryRecord = ProductSaleHistory::where('product_id', $product->id)->whereNull('end_date')->latest()->first();

        $actualSalePrice = $this->calculateSalePrice($product);

        // Если тип распродажи 'nonsale', закрываем текущую распродажу и выходим
        if ($product->sale_type === 'nonsale') {
            if ($lastSaleHistoryRecord && $lastSaleHistoryRecord->end_date === null) {
                $this->closeSale($lastSaleHistoryRecord);
            }
            return;
        }
    
        // Если записи нет, создаем новую
        if (!$lastSaleHistoryRecord) {
            $this->createNewSaleRecord($product, $actualSalePrice);
            return;
        }
    
        // Если последняя запись не завершена и цена изменилась, закрываем и создаем новую запись
        if ($lastSaleHistoryRecord->end_date === null && $lastSaleHistoryRecord->sale_price != $actualSalePrice) {
            $this->closeSale($lastSaleHistoryRecord);
            $this->createNewSaleRecord($product, $actualSalePrice);
        }
    }

    /**
     * Создает новую запись в истории распродаж.
     *
     * @param Product $product Объект товара, для которого создается новая запись.
     * @param float $salePrice Цена продажи, которая будет записана в историю.
     * @return bool Успех вставки записи.
     */
    protected function createNewSaleRecord(Product $product, $salePrice)
    {
        return ProductSaleHistory::insert([
            'product_id' => $product->id,
            'sale_price' => $salePrice,
            'created_at' => now()
        ]);
    }

    /**
     * Закрывает текущую активную запись в истории распродаж, устанавливая дату окончания.
     *
     * @param ProductSaleHistory $saleHistoryRecord Активная запись истории распродаж, которую необходимо закрыть.
     * @return ProductSaleHistory Обновленная запись истории распродаж.
     */
    protected function closeSale(ProductSaleHistory $saleHistoryRecord)
    {
        $saleHistoryRecord->end_date = now();
        $saleHistoryRecord->save();
        return $saleHistoryRecord;
    }

    /**
     * Рассчитывает текущую цену распродажи для товара в зависимости от типа распродажи.
     *
     * @param Product $product Объект товара, для которого рассчитывается цена распродажи.
     * @return float Рассчитанная цена распродажи.
     */
    protected function calculateSalePrice(Product $product)
    {
        switch ($product->sale_type) {
            case 'multiplier':
                return round($product->averagePrice * $product->sale_multiplier, 2);
            case 'auto':
            default:
                return round($product->salePrice, 2);
        }
    }
}
