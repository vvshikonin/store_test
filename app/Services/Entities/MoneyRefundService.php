<?php

namespace App\Services\Entities;

use App\Models\V1\MoneyRefundable;
use App\Services\Transactions\Cash\MoneyRefundCashTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\V1\Expenses\Expense;
use App\Models\V1\Expenses\ExpenseItem;
use App\Services\Transactions\Cash\ExpenseCashTransactions;

class MoneyRefundService
{

    protected $cashTransaction;
    protected $expenseCashTransactions;

    public function __construct(MoneyRefundCashTransaction $cashTransaction)
    {
        $this->cashTransaction = $cashTransaction;
        $this->expenseCashTransactions = app(ExpenseCashTransactions::class);
    }

    /**
     * Обрабатывает логику создания возврата ДС.
     *
     * @param mixed $relatedModel модель со связью moneyRefundable.
     */
    public function create($relatedModel, $debt_sum, $contractor_id, $legal_entity_id, $payment_method_id)
    {
        $refund = $relatedModel->moneyRefundable()->firstOrCreate([
            'debt_sum' => $debt_sum,
            'contractor_id' => $contractor_id,
            'legal_entity_id' => $legal_entity_id,
            'payment_method_id' => $payment_method_id
        ]);

        return $refund;
    }

    /**
     * Обрабатывает логику обновления возврата ДС.
     *
     * @param MoneyRefundable $refund
     * @param array $data
     * @return MoneyRefundable
     */
    public function update($refund, $data)
    {
        return  DB::transaction(function () use ($refund, $data) {
            $refund->fill($data);
            $refund->completed_at = $refund->status ? $data['completed_at'] ?? now() : null;

            if (isset($data['new_incomes'])) $this->createIncomes($refund, $data['new_incomes']);
            if (isset($data['incomes'])) $this->updateIncomes($refund, $data['incomes']);
            if (isset($data['deleted_incomes'])) $this->deleteIncomes($refund, $data['deleted_incomes']);

            $refund->refund_sum_money = $refund->incomesSum();
            $refund->save();

            return $refund;
        });
    }

    /**
     * Создаёт поступление возврата ДС.
     */
    private function createIncomes(MoneyRefundable $refund, array $data): void
    {
        foreach ($data as $newData) {
            $income = $refund->incomes()->create($newData);

            $this->cashTransaction->makeOutcomingTransaction(
                $income,
                $income->sum,
                $income->payment_method_id,
                $income->date
            );
        }
    }

    /**
     * Обновляет поступление возврата ДС.
     */
    private function updateIncomes(MoneyRefundable $refund, array $data): void
    {
        $refund->loadMissing('incomes');
        $incomes = [];
        foreach ($data as $newData) {
            $income = $refund->incomes()->find($newData['id']);
            $income->fill($newData);
            $incomes[] = $income;

            $this->cashTransaction->makeReplaceOutcomingTransactions(
                $income,
                $income->sum,
                $income->payment_method_id,
                $income->date
            );
        }
        $refund->incomes()->saveMany($incomes);
    }

    /**
     * Удаляет поступление возврата ДС.
     */
    private function deleteIncomes(MoneyRefundable $refund, array $data): void
    {
        if (!count($data)) {
            return;
        }

        $deletingIncomes = $refund->incomes()->whereIn('id', $data)->get();
        foreach ($deletingIncomes as $income) {
            $this->cashTransaction->makeRollbackTransactions($income, $this->cashTransaction::OUT);
            $income->delete();
        }
    }

    /**
     * Обрабатывает возврат ДС при смене суммы возврата.
     *
     * @param \App\Models\V1\MoneyRefundable $refund
     * @return \App\Models\V1\MoneyRefundable
     */
    private function handleRefundSumMoneyChange($refund)
    {
        // if ($refund->refund_sum_money != 0) {

        //     if ($refund->refund_sum_money > $refund->getOriginal('refund_sum_money')) {
        //         $this->cashTransaction->makeOutcomingTransaction(
        //             $refund,
        //             $refund->refund_sum_money - $refund->getOriginal('refund_sum_money'),
        //             $refund->payment_method_id
        //         );
        //     } else {
        //         $this->cashTransaction->makeReplaceOutcomingTransactions(
        //             $refund,
        //             $refund->refund_sum_money,
        //             $refund->payment_method_id
        //         );
        //     }
        // } else $this->cashTransaction->makeRollbackTransactions($refund, $this->cashTransaction::OUT);

        // return $refund;
    }

    /**
     * Обрабатывает возврат ДС при смене суммы возврата в товарном эквиваленте.
     *
     * @param \App\Models\V1\MoneyRefundable $refund
     * @return \App\Models\V1\MoneyRefundable
     */
    protected function handleRefundSumProductsChange($refund)
    {
        return $refund;
    }

    /**
     * Обрабатывает логику удаления возврата ДС.
     *
     * @param mixed $relatedModel модель со связью moneyRefundable.
     */
    public function delete($relatedModel)
    {
        DB::transaction(function () use (&$relatedModel) {
            $this->cashTransaction->rollbackCashFlow($relatedModel->moneyRefundable);

            if ($relatedModel->moneyRefundable instanceof MoneyRefundable) {
                // $relatedModel->moneyRefundable()->incomes()->delete();
                $relatedModel->moneyRefundable()->delete();
            }
        });
    }

    /**
     * Маркирует возврат ДС как невыполненный.
     *
     * @param \App\Models\V1\MoneyRefundable $refund
     * @return \App\Models\V1\MoneyRefundable
     */
    public function markAsIncomplete($refund)
    {
        return $this->update($refund, [
            'status' => false,
            'completed_at' => null,
        ]);
    }

    /**
     * Маркирует возврат ДС как выполненный.
     *
     * @param \App\Models\V1\MoneyRefundable $refund
     * @param string|\Carbon\Carbon $completedAt
     * @return \App\Models\V1\MoneyRefundable
     */
    public function markAsCompleted($refund, $completedAt = null)
    {
        return $this->update($refund, [
            'status' => true,
            'completed_at' => $completedAt ?? now(),
        ]);
    }

    public function getAllCompletedRefunds()
    {
        return MoneyRefundable::whereNotNull('completed_at')->get();
    }

    public function convertToExpense(MoneyRefundable $moneyRefund)
    {
        return DB::transaction(function () use ($moneyRefund) {
            $expense = $this->createExpenseFromMoneyRefund($moneyRefund);
            // $this->updateCashTransactions($moneyRefund, $expense);
            $this->expenseCashTransactions->makeCashFlow($expense, $expense->getTotalSum(), $expense->payment_date, $expense->payment_method_id);
            // $this->closeMoneyRefund($moneyRefund);

            return $expense;
        });
    }

    private function createExpenseFromMoneyRefund(MoneyRefundable $moneyRefund)
    {
        // Загружаем связанные данные
        $moneyRefund->load('refundable');

        $expense = new Expense();

        // Проверяем, что связанный объект существует
        $refundable = $moneyRefund->refundable;

        // Определяем тип связанного источника и извлекаем данные
        $expense->legal_entity_id = $refundable->legal_entity_id ?? $moneyRefund->legal_entity_id;
        $expense->payment_method_id = $refundable->payment_method_id ?? $moneyRefund->payment_method_id;
        $expense->payment_date = $refundable->payment_date ?? $moneyRefund->created_at;

        $expense->accounting_month = Carbon::parse($moneyRefund->created_at)->month;
        $expense->accounting_year = Carbon::parse($moneyRefund->created_at)->year;
        $expense->is_paid = true;
        $expense->comment = "Создано из Возврата ДС №{$moneyRefund->id}";
        // $expense->contragent_id = null;
        // $expense->expense_type_id = null;
        $expense->save();

        $expenseItem = new ExpenseItem();
        $expenseItem->expense_id = $expense->id;
        $expenseItem->name = "Возврат ДС №{$moneyRefund->id}";
        $expenseItem->amount = 1;
        $expenseItem->price = $moneyRefund->debt_sum - ($moneyRefund->refund_sum_money + $moneyRefund->refund_sum_products);
        $expenseItem->save();

        // Создаем новое поступление (Income) на сумму, которая поступит в хоз. расход
        $incomeData = [
            'sum' => $expenseItem->price,
            'payment_method_id' => $expense->payment_method_id,
            'date' => $expense->payment_date,
            'is_for_expense' => true, // Устанавливаем флаг is_for_expense в true
        ];
        $this->createIncomes($moneyRefund, [$incomeData]);

        // Отмечаем возврат ДС как оплаченный
        $moneyRefund->refund_sum_money += $expenseItem->price;
        $moneyRefund->status = 2;
        $moneyRefund->completed_at = Carbon::now();
        $moneyRefund->save();

        return $expense;
    }

    // private function updateCashTransactions(MoneyRefundable $moneyRefund, Expense $expense)
    // {
    //     $this->cashTransaction->makeRollbackTransactions($moneyRefund, $this->cashTransaction::OUT);
    //     $this->cashTransaction->makeIncomingTransaction(
    //         $expense,
    //         $expense->sum,
    //         $expense->payment_method_id,
    //         $expense->payment_date
    //     );
    // }

    // private function closeMoneyRefund(MoneyRefundable $moneyRefund)
    // {
    //     // $moneyRefund->status = 'converted';
    //     $moneyRefund->save();
    // }
}
