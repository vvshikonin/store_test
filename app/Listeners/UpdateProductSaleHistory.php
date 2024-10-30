<?php

namespace App\Listeners;

use App\Events\ProductUpdated;
use App\Services\History\ProductSaleHistoryService;

class UpdateProductSaleHistory
{
    protected $productSaleHistoryService;

    public function __construct(ProductSaleHistoryService $productSaleHistoryService)
    {
        $this->productSaleHistoryService = $productSaleHistoryService;
    }

    public function handle(ProductUpdated $event)
    {
        $product = $event->product;

        // Передаем обработку логики в сервис
        $this->productSaleHistoryService->handleProductUpdate($product);
    }
}
