<?php

namespace App\Services\Entities\Defects;

use App\Models\V1\Defect;
use App\Services\Entities\MoneyRefundService;
use App\Services\TransactionService;
use DB;

class DefectService
{
    protected $moneyRefundService;
    protected $transactionService;

    /**
     * Конструктор.
     *
     * @param $moneyRefundService
     * @param $transactionService
     */
    public function __construct(MoneyRefundService $moneyRefundService, TransactionService $transactionService)
    {
        $this->moneyRefundService = $moneyRefundService;
        $this->transactionService = $transactionService;
    }

    /**
     * Обновляет информацию о браке на основе предоставленных данных.
     *
     * @param Defect $defect
     * @param array $data
     * @return Defect
     */
    public function update(Defect &$defect, array $data): Defect
    {
        return DB::transaction(function () use ($defect, $data) {
            $this->fillDefect($defect, $data);
            $this->handleCompletedAt($defect);
            $this->handleMoneyRefundStatus($defect, $data);
            $this->handleTransactions($defect);

            $defect->save();

            return $defect;
        });
    }

    /**
     * Заполняет объект брака данными.
     *
     * @param Defect $defect
     * @param array $data
     */
    private function fillDefect(Defect &$defect, array $data)
    {
        $defect->fill($data);
    }

    /**
     * Обрабатывает дату завершения брака.
     *
     * @param Defect $defect
     */
    private function handleCompletedAt(Defect &$defect)
    {
        if ($defect->isDirty() && $defect->is_completed) {
            $defect->completed_at = now();
        } elseif ($defect->isDirty() && !$defect->is_completed) {
            $defect->completed_at = null;
        }
    }

    /**
     * Обрабатывает статус возврата денег.
     *
     * @param Defect $defect
     * @param array $data
     */
    private function handleMoneyRefundStatus(Defect &$defect, array $data)
    {
        if ($data['money_refund_status'] && !$defect->money_refund_status) {
            $debt_sum = $defect->order->orderProducts->sum(function ($product) {
                return $product->amount * $product->avg_price;
            });
            $contractor_id = $defect->order->orderProducts()->first()->contractor_id;
            $this->moneyRefundService->create(
                $defect,
                $debt_sum,
                $contractor_id,
                $defect->legal_entity_id,
                $defect->payment_method_id,
            );
        } elseif (!$data['money_refund_status'] && $defect->money_refund_status) {
            $this->moneyRefundService->delete($defect);
        }
        $defect->money_refund_status = $data['money_refund_status'];
    }

    /**
     * Обрабатывает транзакции, связанные с браком.
     *
     * @param Defect $defect
     */
    private function handleTransactions(Defect &$defect)
    {
        $transactionCondition = $defect->refund_type == 1 && $defect->is_completed == 1;
        $changed = $defect->isDirty('refund_type') || $defect->isDirty('is_completed');

        if ($transactionCondition && $changed) {
            $orderProducts = $defect->order->orderProducts;
            foreach ($orderProducts as $product) {
                $this->transactionService->makeIncomingTransaction(
                    $defect,
                    $product->amount,
                    $product->product_id,
                    $product->contractor_id,
                    $product->avg_price
                );
            }
        } else if (!$transactionCondition && $changed) {
            $this->transactionService->makeRollbackTransactions($defect);
        }
    }
}
