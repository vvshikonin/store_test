<?php

namespace App\Services\Entities\Expenses;

use App\Models\V1\Expenses\ExpenseSummary;
use App\Models\V1\Expenses\ExpenseSummaryItem;
use App\Models\V1\Expenses\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ExpenseSummaryService
{
    /**
     * Получить все сгруппированные результаты.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSummaries(Request $request)
    {
        $query = ExpenseSummary::orderBy('accounting_year', 'desc')
            ->orderBy('accounting_month', 'desc');

        $filters = $request->all();

        // Log::info('Фильтры: ' . json_encode($filters));
        $query->applyFilters($filters);

        return $query;
    }

    /**
     * Получить все items для указанного ExpenseSummary.
     *
     * @param int $summaryId Идентификатор ExpenseSummary
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getItemsBySummaryId($summaryId, $legalEntityFilter = null)
    {
        $query = ExpenseSummaryItem::where('expense_summary_id', $summaryId);

        if ($legalEntityFilter)
        {
            $query->whereHas('expense', function ($query) use ($legalEntityFilter) {
                $query->whereIn('legal_entity_id', $legalEntityFilter);
            });
        }

        return $query->with('expense.items')->get();
    }

    /**
     * Обновить значение дохода для указанного ExpenseSummary.
     *
     * @param int $summaryId Идентификатор ExpenseSummary
     * @param float $totalIncome Значение дохода
     * @return \App\Models\V1\Expenses\ExpenseSummary
     */
    public function updateIncome($summaryId, $totalIncome)
    {
        $summary = ExpenseSummary::findOrFail($summaryId);
        $summary->total_income = $totalIncome;
        $summary->save();
        return $summary;
    }

    /**
     * Удалить указанный ExpenseSummary и все связанные с ним items.
     *
     * @param int $summaryId Идентификатор ExpenseSummary
     * @return void
     */
    public function deleteSummary($summaryId)
    {
        ExpenseSummary::where('id', $summaryId)->delete();
    }

    /**
     * Генерация новых данных, обновление существующих ExpenseSummary и пересоздание ExpenseSummaryItem.
     *
     * @return void
     */
    public function generateSummaries()
    {
        // Удаляем старые items
        ExpenseSummaryItem::truncate();

        // Получаем оплаченные расходы с непустыми типом расхода, месяцем и годом учета
        $expenses = Expense::where('is_paid', true)
            ->whereNotNull('expense_type_id')
            ->whereNotNull('accounting_month')
            ->whereNotNull('accounting_year')
            ->get();

        // Обнуляем total_expenses для всех сводок перед пересозданием
        ExpenseSummary::query()->update(['total_expenses' => 0]);

        foreach ($expenses as $expense) {
            $summary = ExpenseSummary::firstOrCreate(
                [
                    'accounting_month' => $expense->accounting_month,
                    'accounting_year' => $expense->accounting_year
                ]
            );

            $expenseTotal = $expense->getTotalSum();
            // Log::info('Общая сумма для расхода (ID: ' . $expense->id . '): ' . $expenseTotal); // Логирование суммы расхода
            // Log::info('Текущая сумма total_expenses до добавления: ' . $summary->total_expenses); // Логирование текущего значения
            $summary->total_expenses += $expenseTotal;
            // Log::info('Новая сумма total_expenses после добавления: ' . $summary->total_expenses); // Логирование нового значения
            $summary->save();

            ExpenseSummaryItem::create([
                'expense_summary_id' => $summary->id,
                'expense_id' => $expense->id
            ]);
        }

        return true;
    }
}
