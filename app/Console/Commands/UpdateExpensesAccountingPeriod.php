<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\Expenses\Expense;
use Carbon\Carbon;

class UpdateExpensesAccountingPeriod extends Command
{
    // Название и описание команды
    protected $signature = 'expenses:update-accounting-period';
    protected $description = 'Обновляет accounting_year и accounting_month для расходов с контрагентами, у которых is_period_coincides = true';

    /**
     * Выполнение команды.
     *
     * @return int
     */
    public function handle()
    {
        // Получаем все расходы с контрагентами, у которых is_period_coincides = true
        $expenses = Expense::whereHas('contragent', function ($query) {
            $query->where('is_period_coincides', true);
        })->get();

        // Обновляем поля accounting_year и accounting_month для каждого расхода
        foreach ($expenses as $expense) {
            $paymentDate = Carbon::parse($expense->payment_date);
            $expense->accounting_year = $paymentDate->year;
            $expense->accounting_month = $paymentDate->month;
            $expense->save();
        }

        $this->info('Обновление завершено успешно.');

        return 0;
    }
}