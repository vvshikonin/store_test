<?php

namespace App\Services\Transactions\Products;

use App\Services\TransactionService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class ContractorRefundTransactions extends TransactionService
{
    /**
     * Создаёт списание товара.
     *
     * @param App\Modeles\V1\ContractorRefundProduct $contractorRefundProduct
     */
    public function makeContractorRefundOutcomingFlow($contractorRefundProduct)
    {
        Log::debug('попал в makeContractorRefundOutcomingFlow');
        Log::debug(print_r($contractorRefundProduct->contractorRefundStocks, true));
        foreach ($contractorRefundProduct->contractorRefundStocks as $contractorRefundStock)
        {
            Log::debug('я в цикле');
            Log::debug('в цикле сейчас: ' . $contractorRefundStock);
            Log::debug($contractorRefundStock->stock->product->id);
            Log::debug($contractorRefundStock->stock->contractor_id);
            Log::debug($contractorRefundStock->stock->price);

            $this->makeOutcomingTransaction(
                /*1*/ $contractorRefundProduct,
                /*2*/ $contractorRefundStock->amount,
                /*3*/ $contractorRefundStock->stock->product->id,
                /*4*/ $contractorRefundStock->stock->contractor_id,
                /*5*/ $contractorRefundStock->stock->price
            );
        }
    }

    /**
     * Отменяет поступление товара.
     *
     * @param App\Modeles\V1\ContractorRefundProduct $contractorRefundProduct
     */
    public function rollbackProductFlow($contractorRefundProduct)
    {
        $this->makeRollbackTransactions($contractorRefundProduct);
    }
}
