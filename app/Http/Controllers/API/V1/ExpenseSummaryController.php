<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\Expenses\ExpenseSummary;
use App\Models\V1\Expenses\ExpenseSummaryItem;
use App\Models\V1\Expenses\ExpenseType;
use App\Services\Entities\Expenses\ExpenseSummaryService;
use App\Http\Resources\V1\Expense\ExpenseSummaryCollection;
use App\Http\Controllers\API\V1\ExpenseTypeController;
use App\Exports\Expenses\ExpenseSummaryRegularExport;
use App\Exports\Expenses\ExpenseSummaryDetailedExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseSummaryController extends Controller
{
    protected $expenseSummaryService;

    public function __construct(ExpenseSummaryService $expenseSummaryService)
    {
        $this->expenseSummaryService = $expenseSummaryService;
    }

    /**
     * Получить все сгруппированные результаты (без items).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // $generateResult = $this->generate();
        $query = $this->expenseSummaryService->getAllSummaries($request);
        // return response()->json($summaries);

        // Применяем сортировку.
        $sortField = $request->get('sort_field', 'id');
        $sortType = $request->get('sort_type', 'asc');
        $query = $query->orderBy($sortField, $sortType);

        // Применяем пагинацию.
        $perPage = $request->get('per_page', 1000);
        $summaries = $query->paginate($perPage);

        return (new ExpenseSummaryCollection($summaries));
    }

    /**
     * Получить items для одного сгруппированного результата.
     *
     * @param int $id Идентификатор ExpenseSummary
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $items = $this->expenseSummaryService->getItemsBySummaryId($id, $request->get('legalEntityFilter'));
        return response()->json($items);
    }

    /**
     * Обновить доход для сгруппированного результата.
     *
     * @param \Illuminate\Http\Request $request HTTP запрос
     * @param int $id Идентификатор ExpenseSummary
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'total_income.total_income' => 'required|numeric'
        ]);

        $totalIncome = $validated['total_income']['total_income'];

        $summary = $this->expenseSummaryService->updateIncome($id, $totalIncome);
        return response()->json($summary);
    }

    /**
     * Генерация отчёта (обновление сгруппированных данных и их items).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate()
    {
        if ($this->expenseSummaryService->generateSummaries()) {
            return response()->json(['message' => 'Отчёт успешно сгенерирован'], 200);
        } else {
            return false;
        }
    }

    /**
     * Экспорт сгруппированного отчёта.
     *
     * @param \Illuminate\Http\Request $request HTTP запрос
     * @return \Illuminate\Http\Response
     */
    public function exportRegular(Request $request)
    {
        // Получаем отфильтрованные и отсортированные данные с помощью метода index
        $query = $this->expenseSummaryService->getAllSummaries($request);

        // Применяем сортировку
        $sortField = $request->get('sort_field', 'id');
        $sortType = $request->get('sort_type', 'asc');
        $query = $query->orderBy($sortField, $sortType);

        // Получаем все результаты без пагинации
        $summaries = $query->get();

        // Получаем типы расходов
        $expenseTypes = ExpenseType::orderBy('sort_order')->get();
        $legalEntityFilter = $request->get('legal_entity_filter');

        // Проходимся по каждому ExpenseSummary
        foreach ($summaries as $summary)
        {
            // Получаем связанные ExpenseSummaryItems для текущего ExpenseSummary
            $summaryItemsQuery = ExpenseSummaryItem::where('expense_summary_id', $summary->id);

            if ($legalEntityFilter)
            {
                $summaryItemsQuery->whereHas('expense', function ($query) use ($legalEntityFilter) {
                    $query->whereIn('legal_entity_id', $legalEntityFilter);
                });
            }
    
            $summaryItems = $summaryItemsQuery->with('expense.items')->get();

            // Суммируем расходы по типам
            $expensesByType = [];
            foreach ($expenseTypes as $type)
            {
                $expensesByType[$type->name] = 0;
            }
            // dd($summaryItems);
            foreach ($summaryItems as $summaryItem)
            {
                // Получаем связанный Expense
                $expense = $summaryItem->expense;
                if ($expense) {
                    // Получаем тип расхода
                    $expenseType = $expenseTypes->firstWhere('id', $expense->expense_type_id);
                    if ($expenseType) {
                        // Проходимся по связанным ExpenseItems
                        foreach ($expense->items as $expenseItem) {
                            // Проверяем, есть ли у элемента цена и она больше 0
                            if (isset($expenseItem->price) && $expenseItem->price > 0) {
                                $expensesByType[$expenseType->name] += $expenseItem->price;
                            }
                        }
                    }
                }
            }

            // Добавляем суммы по типам расходов к текущему ExpenseSummary
            $summary->expenses_by_type = $expensesByType;
        }

        // Передаем полученные данные в класс экспорта
        return Excel::download(new ExpenseSummaryRegularExport($summaries), 'expense_summary_regular.xlsx');
    }

    /**
     * Экспорт детализированного отчёта.
     *
     * @param \Illuminate\Http\Request $request HTTP запрос
     * @return \Illuminate\Http\Response
     */
    public function exportDetailed(Request $request)
    {
        // Получаем отфильтрованные и отсортированные данные с помощью метода index
        $query = $this->expenseSummaryService->getAllSummaries($request);

        // Применяем сортировку
        $sortField = $request->get('sort_field', 'id');
        $sortType = $request->get('sort_type', 'asc');
        $query = $query->orderBy($sortField, $sortType);

        // Получаем все результаты без пагинации
        $summaries = $query->get();

        // Получаем типы расходов
        $expenseTypes = ExpenseType::orderBy('sort_order')->get();
        $legalEntityFilter = $request->get('legal_entity_filter');

        // Проходимся по каждому ExpenseSummary
        foreach ($summaries as $summary) {
            // Получаем связанные ExpenseSummaryItems для текущего ExpenseSummary
            $summaryItemsQuery = ExpenseSummaryItem::where('expense_summary_id', $summary->id);

            if ($legalEntityFilter)
            {
                $summaryItemsQuery->whereHas('expense', function ($query) use ($legalEntityFilter) {
                    $query->whereIn('legal_entity_id', $legalEntityFilter);
                });
            }
    
            $summaryItems = $summaryItemsQuery->with('expense.items')->get();

            // Суммируем расходы по типам и собираем детализированные данные
            $expensesByType = [];
            foreach ($expenseTypes as $type) {
                $expensesByType[$type->name] = [
                    'total' => 0,
                    'items' => []
                ];
            }

            foreach ($summaryItems as $summaryItem) {
                // Получаем связанный Expense
                $expense = $summaryItem->expense;
                if ($expense) {
                    // Получаем тип расхода
                    $expenseType = $expenseTypes->firstWhere('id', $expense->expense_type_id);
                    if ($expenseType) {
                        // Проходимся по связанным ExpenseItems
                        foreach ($expense->items as $expenseItem) {
                            // Проверяем, есть ли у элемента цена и она больше 0
                            if (isset($expenseItem->price) && $expenseItem->price > 0) {
                                $expensesByType[$expenseType->name]['total'] += $expenseItem->price;
                                $expensesByType[$expenseType->name]['items'][] = [
                                    'date' => $expense->payment_date,
                                    'price' => $expenseItem->price
                                ];
                            }
                        }
                    }
                }
            }

            // Добавляем детализированные данные по типам расходов к текущему ExpenseSummary
            $summary->expenses_by_type = $expensesByType;
        }

        // Передаем полученные данные в класс экспорта
        return Excel::download(new ExpenseSummaryDetailedExport($summaries), 'expense_summary_detailed.xlsx');
    }

    /**
     * Удалить один сгруппированный результат и все связанные с ним items.
     *
     * @param int $id Идентификатор ExpenseSummary
     * @return void
     */
    private function destroy($id)
    {
        $this->expenseSummaryService->deleteSummary($id);
    }
}
