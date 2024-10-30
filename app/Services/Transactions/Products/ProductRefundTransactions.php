<?php

namespace App\Services\Transactions\Products;

use App\Services\TransactionService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductRefundTransactions extends TransactionService
{
    /**
     * Обрабатывает логику создания транзакции списания.
     *
     * @param App\Models\V1\ProductRefund $productRefund
     */

    public function productRefundOutcoming($productRefund)
    {
        $orderProducts = $productRefund->orderProducts;
        foreach($orderProducts as $product) {
            $this->makeOutcomingTransaction(
                $product,
                $product->amount,
                $product->product_id,
                $product->contractor_id
            );
        }
    }

    /**
     * Обрабатывает логику создания транзакции поступления.
     *
     * @param App\Models\V1\ProductRefund $productRefund
     */

    public function productRefundIncoming($productRefund)
    {
        $orderProducts = $productRefund->orderProducts;
        foreach($orderProducts as $product) {
            $this->makeIncomingTransaction(
                $product,
                $product->amount,
                $product->product_id,
                $product->contractor_id,
                $product->avg_price
            );
        }
    }

    /**
     * Обрабатывает логику отката транзакции списания.
     *
     * @param App\Models\V1\ProductRefund $productRefund
     */

    public function rollbackOutcomingProductRefund($productRefund)
    {
        $orderProducts = $productRefund->orderProducts;
        foreach($orderProducts as $product) {
            $this->makeRollbackTransactions($product, $this::OUT);
        }
    }

    /**
     * Обрабатывает логику отката транзакции поступления.
     *
     * @param App\Models\V1\ProductRefund $productRefund
     */

    public function rollbackIncomingProductRefund($productRefund)
    {
        $orderProducts = $productRefund->orderProducts;
        foreach($orderProducts as $product) {
            $this->makeRollbackTransactions($product, $this::IN);
        }
    }
}