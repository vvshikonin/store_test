<?php

namespace App\Services\Entities\ProductRefunds;

use App\Services\Entities\MoneyRefundService;
use Illuminate\Support\Facades\DB;
use App\Services\Transactions\Products\ProductRefundTransactions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductRefundService
{
    private $productRefundTransactions;

    public function __construct(ProductRefundTransactions $productRefundTransactions)
    {
        $this->productRefundTransactions = $productRefundTransactions;
    }

    /**
     * Обрабатывает логику обновления возврата товаров.
     *
     * @param App\Models\V1\ProductRefund $productRefund
     * @param array $data
     * @return App\Models\V1\ProductRefund
     */

    public function update($productRefund, $data)
    {
        DB::transaction(function () use (&$productRefund, $data) {
            $productRefund->fill($data);
            $productRefund->delivery_date = date('Y-m-d H:i:s');

            if ($productRefund->status && $productRefund->isDirty('status')) {
                $productRefund->completed_at = date('Y-m-d H:i:s');
                $this->productRefundTransactions->productRefundIncoming($productRefund);
            } elseif (!$productRefund->status) {
                $productRefund->completed_at = null;
                $this->productRefundTransactions->rollbackIncomingProductRefund($productRefund);
            }

            $productRefund->save();
        });

        return $productRefund->refresh();
    }
}
