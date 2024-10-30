<?php

namespace App\Services\Entities;

use App\Models\V1\ContractorRefund;
use App\Models\V1\ContractorRefundProduct;
use App\Models\V1\ContractorRefundStock;
use App\Models\V1\InvoiceProduct;
use App\Services\Transactions\Products\ContractorRefundTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Entities\MoneyRefundService;

class ContractorRefundService
{
    protected $productTransaction;
    protected $moneyRefundService;

    public function __construct(ContractorRefundTransactions $productTransaction, MoneyRefundService $moneyRefundService)
    {
        $this->productTransaction = $productTransaction;
        $this->moneyRefundService = $moneyRefundService;
    }

    /**
     * Создать новый экземпляр `ContractorRefund` на основе входных данных.
     *
     * @param array $data Валидированные данные из запроса.
     * @return ContractorRefund Созданный экземпляр ContractorRefund.
     */
    public function create(array $data): ContractorRefund
    {
        return DB::transaction(function () use ($data) {
            $refund = ContractorRefund::create($data);

            foreach ($data['products'] as $dataProduct) {
                $this->createRefundProduct($refund, $dataProduct);
            }

            return $refund;
        });
    }

    public function update(ContractorRefund &$contractorRefund, array $data)
    {
        return DB::transaction(function () use (&$contractorRefund, $data) {
            $contractorRefund->fill($data);

            if (!$contractorRefund->is_complete && $data['is_complete']) {
                $contractorRefund->is_complete = 1;
                $contractorRefund->completed_at = now();

                $debtSum = $contractorRefund->contractorRefundProducts->sum(function ($product) {
                    return $product->amount * $product->invoiceProduct->price;
                });

                $this->moneyRefundService->create(
                    $contractorRefund,
                    $debtSum,
                    $contractorRefund->invoice->contractor_id,
                    $contractorRefund->invoice->legal_entity_id,
                    $contractorRefund->invoice->payment_method_id,
                );
                
            } elseif ($contractorRefund->is_complete && !$data['is_complete']) {
                $contractorRefund->is_complete = 0;
                $contractorRefund->completed_at = null;
                $this->moneyRefundService->delete($contractorRefund);
            }

            if (isset($data['deleted_contractor_refund_products_ids'])) {
                foreach ($data['deleted_contractor_refund_products_ids'] as $deletedProductId) {
                    $deletedProduct = ContractorRefundProduct::find($deletedProductId);
                    $this->deleteRefundProduct($deletedProduct);
                }
            }

            if (isset($data['contractor_refund_products'])) {
                foreach ($data['contractor_refund_products'] as $product) {
                    if ($product['id'] === 'new') {
                        $this->createRefundProduct($contractorRefund, $product);
                    }
                }
            }

            $contractorRefund->save();
            return $contractorRefund;
        });
    }

    private function deleteRefundProduct(ContractorRefundProduct $deletedProduct)
    {
        $deletedProduct->invoiceProduct()->decrement('refunded', $deletedProduct->amount);
        $this->productTransaction->rollbackProductFlow($deletedProduct);

        $deletedProduct->contractorRefundStocks()->delete();
        $deletedProduct->delete();
    }

    public function delete(ContractorRefund $contractorRefund)
    {
        return DB::transaction(function () use ($contractorRefund) {
            $contractorRefund->moneyRefundable?->delete();

            foreach ($contractorRefund->contractorRefundProducts as $product) {
                $this->deleteRefundProduct($product);
            }

            $contractorRefund->delete();
        });
    }

    /**
     * Создать `ContractorRefundProduct` и его связанные `ContractorRefundStock`.
     *
     * @param ContractorRefund $refund
     * @param array $dataProduct
     * @return ContractorRefundProduct
     */
    private function createRefundProduct(ContractorRefund $refund, array $dataProduct): ContractorRefundProduct
    {
        $dataProduct['contractor_refund_id'] = $refund->id;
        $product = ContractorRefundProduct::create($dataProduct);

        $invoiceProduct = InvoiceProduct::find($dataProduct['invoice_product_id']);
        if ($invoiceProduct->received < ($invoiceProduct->refunded + $dataProduct['amount'])) {
            throw new \Exception('Количество возвращаемого товара не может превышать количество оприходованного товара.');
        }

        $invoiceProduct->increment('refunded', $dataProduct['amount']);

        foreach ($dataProduct['stocks'] as $dataStock) {
            $this->createRefundStock($product, $dataStock);
        }

        $this->productTransaction->makeContractorRefundOutcomingFlow($product);

        return $product;
    }

    /**
     * Создать `ContractorRefundStock`.
     *
     * @param ContractorRefundProduct $product
     * @param array $dataStock
     * @return ContractorRefundStock
     */
    private function createRefundStock(ContractorRefundProduct $product, array $dataStock): ContractorRefundStock
    {
        $dataStock['contractor_refund_product_id'] = $product->id;

        $contractorRefundStocks = ContractorRefundStock::create($dataStock);

        return $contractorRefundStocks;
    }
}
