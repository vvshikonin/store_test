<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Entities\Expenses\ExpenseService;
use App\Services\Entities\Expenses\ExpenseTypeService;
use App\Http\Resources\V1\Expense\ExpenseCollection;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Expense\ExpenseStoreRequest;
use App\Exports\Expenses\ExpenseItemsExport;
use App\Exports\Expenses\SortedExpenseItemsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\V1\Expense\ExpenseResource;
use Illuminate\Support\Facades\Storage;
use App\Models\V1\Expenses\Expense;

class ExpenseController extends Controller
{
    protected $expenseService;
    protected $expenseTypeService; // Добавлено новое свойство для ExpenseTypeService

    public function __construct(ExpenseService $expenseService, ExpenseTypeService $expenseTypeService)
    {
        $this->expenseService = $expenseService;
        $this->expenseTypeService = $expenseTypeService; // Инъекция ExpenseTypeService через конструктор
    }

    public function index(Request $request)
    {
        // Получаем запрос из сервиса.
        $query = $this->expenseService->getAllExpensesQuery($request);

        // Рассчитываем общую сумму до применения пагинации.
        $totalSum = $this->expenseService->calculateTotalSum($query);

        // Применяем сортировку.
        $sortField = $request->get('sort_field', 'id');
        $sortType = $request->get('sort_type', 'asc');
        $query = $query->orderBy($sortField, $sortType);

        // Проверяем, есть ли запрос на все данные
        if ($request->get('no_paginate') == 'true') {
            $expenses = $query->get();
            return (new ExpenseCollection($expenses, $totalSum));
        }

        // Применяем пагинацию.
        $perPage = $request->get('per_page', 15);
        $expenses = $query->paginate($perPage);

        // Возвращаем результат в виде ресурса коллекции вместе с общей суммой.
        return (new ExpenseCollection($expenses, $totalSum));
    }

    public function show($id)
    {
        return $this->expenseService->getExpenseById($id);
    }

    public function store(ExpenseStoreRequest $request)
    {
        return $this->expenseService->createExpense($request->all());
    }

    public function createFastExpense(Request $request)
    {
        $request->merge(['is_need_to_complete' => true]);
        return $this->expenseService->createExpense($request->all());
    }

    public function convertToMoneyRefund(Request $request)
    {
        return $this->expenseService->convertToMoneyRefund($request->all());
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        return $this->expenseService->updateExpense($expense, $request->all());
    }

    public function destroy(Expense $expense)
    {
        return $this->expenseService->deleteExpense($expense->id);
    }

    public function exportExpenseItems(Request $request)
    {
        $expensesQuery = $this->expenseService->getAllExpensesQuery($request);
        $expenses = $expensesQuery->orderBy($request->sort_field, $request->sort_type)->get();

        return Excel::download(new ExpenseItemsExport($expenses), 'expense_items.xlsx');
    }

    public function exportSortedExpenseItems(Request $request)
    {
        // Получаем отфильтрованный запрос расходов с учетом фильтров, переданных в $request.
        $expensesQuery = $this->expenseService->getAllExpensesQuery($request);

        // Получение всех результатов запроса. Метод get() в Laravel не требует аргументов.
        $expenses = $expensesQuery->get(['*']); // Извлекаем все атрибуты

        // Создание экземпляра экспорта, передавая полученные расходы. 
        // Предполагается, что SortedExpenseItemsExport адаптирован для работы с этими данными.
        $export = new SortedExpenseItemsExport($expenses, $this->expenseTypeService);

        // Инициирование скачивания файла экспорта.
        return Excel::download($export, 'sorted_expense_items.xlsx');
    }

    public function uploadFile(Request $request, $expenseId)
    {
        $file = $request->file('file');

        Log::info($file);

        $expense = $this->expenseService->uploadExpenseFile($expenseId, $file);

        return response()->json(['data' => $expense]);
    }

    public function uploadInvoiceFile(Request $request, $expenseId)
    {
        $file = $request->file('invoice_file');

        Log::info($file);

        $expense = $this->expenseService->uploadInvoiceFile($expenseId, $file);

        return response()->json(['data' => $expense]);
    }

    public function deleteFile($expenseId)
    {
        Log::debug("Получен запрос на удаление файла для expense с ID: {$expenseId}");
        $expense = Expense::findOrFail($expenseId);

        $fileData = json_decode($expense->files, true);
        $filePath = $fileData['path'] ?? null;

        if ($filePath) {
            Log::info("Попытка удаления файла: {$filePath}");
            if (Storage::disk('public')->delete($filePath)) {
                Log::info("Файл {$filePath} успешно удалён.");

                $expense->files = null;
                if ($expense->save()) {
                    Log::info("Данные файла для expense с ID {$expense->id} успешно обновлены в базе данных.");
                } else {
                    Log::warning("Не удалось обновить данные файла для expense с ID {$expense->id}.");
                    // Прямое обновление поля files в базе данных
                    $updated = \DB::table('expenses')->where('id', $expense->id)->update(['files' => null]);
                    if ($updated) {
                        Log::info("Данные файла для expense с ID {$expense->id} обновлены через Query Builder.");
                    } else {
                        Log::warning("Обновление через Query Builder не удалось для expense с ID {$expense->id}.");
                    }
                }
            } else {
                Log::warning("Не удалось удалить файл {$filePath} с диска.");
            }
        } else {
            Log::info("Файл для удаления не найден или уже удалён для expense с ID: {$expenseId}");
        }

        // Дополнительная проверка для подтверждения обновления
        $updatedExpense = Expense::findOrFail($expenseId);
        if (is_null($updatedExpense->files)) {
            Log::debug("Подтверждение: поле 'files' для expense с ID {$expenseId} успешно обнулено.");
        } else {
            Log::debug("Ошибка: поле 'files' для expense с ID {$expenseId} по-прежнему содержит данные.");
        }

        return $this->expenseService->getExpenseById($expense->id);
    }

    public function deleteInvoiceFile($expenseId)
    {
        Log::debug("Получен запрос на удаление файла чека/ТТН для expense с ID: {$expenseId}");
        $expense = Expense::findOrFail($expenseId);

        $fileData = json_decode($expense->invoice_file, true);
        $filePath = $fileData['path'] ?? null;

        if ($filePath) {
            Log::info("Попытка удаления файла чека/ТТН: {$filePath}");
            if (Storage::disk('public')->delete($filePath)) {
                Log::info("Файл чека/ТТН {$filePath} успешно удалён.");

                $expense->invoice_file = null;
                if ($expense->save()) {
                    Log::info("Данные файла чека/ТТН для expense с ID {$expense->id} успешно обновлены в базе данных.");
                } else {
                    Log::warning("Не удалось обновить данные файла чека/ТТН для expense с ID {$expense->id}.");
                    // Прямое обновление поля invoice_file в базе данных
                    $updated = \DB::table('expenses')->where('id', $expense->id)->update(['invoice_file' => null]);
                    if ($updated) {
                        Log::info("Данные файла чека/ТТН для expense с ID {$expense->id} обновлены через Query Builder.");
                    } else {
                        Log::warning("Обновление через Query Builder не удалось для expense с ID {$expense->id}.");
                    }
                }
            } else {
                Log::warning("Не удалось удалить файл чека/ТТН {$filePath} с диска.");
            }
        } else {
            Log::info("Файл чека/ТТН для удаления не найден или уже удалён для expense с ID: {$expenseId}");
        }

        // Дополнительная проверка для подтверждения обновления
        $updatedExpense = Expense::findOrFail($expenseId);
        if (is_null($updatedExpense->invoice_file)) {
            Log::debug("Подтверждение: поле 'invoice_file' для expense с ID {$expenseId} успешно обнулено.");
        } else {
            Log::debug("Ошибка: поле 'invoice_file' для expense с ID {$expenseId} по-прежнему содержит данные.");
        }

        return $this->expenseService->getExpenseById($expense->id);
    }
}
