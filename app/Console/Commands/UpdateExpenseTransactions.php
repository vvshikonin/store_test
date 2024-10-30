<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Entities\Expenses\ExpenseService;
use DB;

class UpdateExpenseTransactions extends Command
{
    protected $signature = 'expenses:update-transactions';
    protected $description = 'Обновление транзакций для всех оплаченных хозяйственных расходов';

    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        parent::__construct();
        $this->expenseService = $expenseService;
    }

    public function handle()
    {
        DB::transaction(function () {
            $expenses = $this->expenseService->getAllPaidExpenses();
            foreach ($expenses as $expense) {
                $this->info("Обновление расхода с ID: {$expense->id}");

                // Сохраняем дату оплаты
                $paymentDate = $expense->payment_date;

                // Сбрасываем флаг is_paid, не затрагивая payment_date
                $this->expenseService->markAsUnpaid($expense->id);

                // Устанавливаем флаг is_paid обратно в true, передавая сохраненную дату оплаты
                $this->expenseService->markAsPaid($expense->id, $paymentDate);

                $this->info("Расход обновлён! ID: {$expense->id}");
            }
        });

        $this->info("Обновление транзакций завершено.");
    }
}
