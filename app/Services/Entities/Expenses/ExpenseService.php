<?php

namespace App\Services\Entities\Expenses;

use App\Models\V1\Expenses\Expense;
use App\Models\V1\Expenses\ExpenseItem;
use App\Models\V1\MoneyRefundable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\Transactions\Cash\ExpenseCashTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    protected $cashTransactions;

    // Конструктор для инъекции сервиса транзакций
    public function __construct(ExpenseCashTransactions $cashTransactions)
    {
        $this->cashTransactions = $cashTransactions;
    }

    /**
     * Get all expenses.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllExpensesQuery(Request $request)
    {
        // Создаем запрос с модели Expense.
        $query = Expense::with('creator');

        // Применяем фильтры к запросу.
        $filters = $request->all();
        $query->applyFilters($filters);

        // Возвращаем запрос
        return $query;
    }

    /**
     * Получение всех оплаченных расходов.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPaidExpenses()
    {
        return Expense::where('is_paid', true)->get();
    }

    /**
     * Сброс статуса оплаты расхода.
     *
     * @param int $expenseId ID расхода
     */
    public function markAsUnpaid($expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        $data = [
            'is_paid' => false,
            'updated_by' => 1
        ];

        // Обновляем расход, используя существующий метод updateExpense
        $this->updateExpense($expense, $data);
    }

    /**
     * Установка статуса оплаты расхода в "оплачено".
     *
     * @param int $expenseId ID расхода
     */
    public function markAsPaid($expenseId, $paymentDate = null)
    {
        $expense = Expense::findOrFail($expenseId);
        $data = [
            'is_paid' => true,
            'payment_date' => $paymentDate,
            'updated_by' => 1
        ];

        // Обновляем расход, используя существующий метод updateExpense
        $this->updateExpense($expense, $data);
    }

    /**
     * Get an expense by its ID.
     *
     * @param int $id
     * @return Expense|null
     */
    public function getExpenseById($id)
    {
        $expense = Expense::with('creator', 'updater', 'items')->find($id);

        if ($expense)
        {
            $moneyRefundable = MoneyRefundable::where('converted_to_expense_id', $id)->first();
            $expense->is_converted = !is_null($moneyRefundable);
        }

        return $expense;
    }

    /**
     * Create a new expense.
     *
     * @param array $data
     * @return Expense
     */
    public function createExpense($data)
    {
        if ($data['is_need_to_complete'])
        {
            $data['payment_method_id'] = 1;
            $data['legal_entity_id'] = 1;

            $expense = Expense::create($data);

            $this->uploadExpenseFile($expense->id, $data['file']);
            return $expense;
        }

        $duplicateExists = Expense::where('contragent_id', $data['contragent_id'])
                ->where('accounting_month', $data['accounting_month'])
                ->where('accounting_year', $data['accounting_year'])
                ->where('payment_date', $data['payment_date'])
                ->whereHas('items', function ($query) use ($data) {
                    $query->select(DB::raw('SUM(price) as total_price'))
                        ->groupBy('expense_id')
                        ->having('total_price', '=', array_sum(array_column($data['items'], 'price')));
                })
                ->exists();

        if ($duplicateExists)
            return response()->json(['message' => 'Расход с такими данными уже существует.'], 422);

        return DB::transaction(function () use (&$expense, $data) {
            $expense = new Expense;

            // Установка payment_date, если is_paid = 1 и payment_date не предоставлено
            if (!empty($data['is_paid'] == 1) && empty($data['payment_date'])) {
                $data['payment_date'] = now()->toDateString(); // Форматирование даты на сегодняшний день
            }

            // Установка expense_type_id, если есть элементы
            if (isset($data['items']) && is_array($data['items']) && !empty($data['items'])) {
                $data['expense_type_id'] = $data['items'][0]['expense_type_id'];
            }

            $expense->fill($data);
            $expense->save(); // Сохраняем основные данные о расходе

            // Если в данных есть элементы расхода, сохраняем их
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $expense->items()->create($item);
                }
            }

            // Если расход оплачен, фиксируем поступление
            if ($expense->is_paid) {
                $this->cashTransactions->makeCashFlow($expense, $expense->getTotalSum(), $data['payment_date'] ?? null);
            }

            return $expense;
        });
    }

    /**
     * Update an existing expense.
     *
     * @param int $id
     * @param array $data
     * @return Expense
     */
    public function updateExpense($expense, array $data)
    {
        return DB::transaction(function () use ($expense, $data) {
            $originalIsPaid = $expense->getOriginal('is_paid') == 1;
            $newIsPaid = $data['is_paid'] == 1 ?? $originalIsPaid;
            $originalPaymentDate = $expense->getOriginal('payment_date');
            $newPaymentDate = $data['payment_date'] ?? $originalPaymentDate;
            $itemsChanged = false;

            // Добавляем проверку для способа оплаты и юридического лица
            $originalPaymentMethodId = $expense->getOriginal('payment_method_id');
            $newPaymentMethodId = $data['payment_method_id'] ?? $originalPaymentMethodId;
            $originalLegalEntityId = $expense->getOriginal('legal_entity_id');
            $newLegalEntityId = $data['legal_entity_id'] ?? $originalLegalEntityId;

            // Обновляем данные расхода после обработки оплаты
            $expense->update($data);

            if (isset($data['converted_to_money_refunds_id']))
            {
                $this->syncConvertedMoneyRefund($data);
            }

            // Обрабатываем связанные элементы расхода
            if (isset($data['items']) && is_array($data['items']))
            {
                $existingItemIds = $expense->items()->pluck('id')->toArray();
                $newItemIds = array_column($data['items'], 'id');
                $toDelete = array_diff($existingItemIds, $newItemIds);
                ExpenseItem::destroy($toDelete);

                foreach ($data['items'] as $itemData)
                {
                    if (isset($itemData['id']))
                    {
                        $expenseItem = ExpenseItem::find($itemData['id']);
                        if ($expenseItem)
                        {
                            // Проверяем, изменился ли price
                            $originalPrice = $expenseItem->getOriginal('price');
                            $newPrice = $itemData['price'] ?? $originalPrice;

                            $expenseItem->update($itemData);

                            if ($newPrice != $originalPrice)
                                // Если цена изменилась, обновляем транзакцию
                                $this->cashTransactions->replaceCashFlow($expense, $expense->getTotalSum(), $newPaymentDate, $newPaymentMethodId);
                        }
                    } else {
                        $expense->items()->create($itemData);
                        $itemsChanged = true;
                    }
                }
            }

            // Проверяем, изменилось ли количество позиций
            if (count($existingItemIds) != count($newItemIds))
                $itemsChanged = true;

            // Проверяем, изменилось ли состояние оплаты, дата оплаты, способ оплаты или юр. лицо
            if ($newIsPaid != $originalIsPaid || $newPaymentDate !== $originalPaymentDate || $newPaymentMethodId !== $originalPaymentMethodId || $newLegalEntityId !== $originalLegalEntityId || $itemsChanged) {
                if ($newIsPaid && !$originalIsPaid) {
                    // Если расход стал оплаченным, создаем новую транзакцию
                    $this->cashTransactions->makeCashFlow($expense, $expense->getTotalSum(), $newPaymentDate, $newPaymentMethodId);
                } elseif (!$newIsPaid && $originalIsPaid) {
                    // Если расход перестал быть оплаченным, отменяем транзакцию
                    $this->cashTransactions->rollbackCashFlow($expense);
                } elseif ($newIsPaid && $originalIsPaid && ($newPaymentDate || $newPaymentMethodId !== $originalPaymentMethodId || $newLegalEntityId !== $originalLegalEntityId || $itemsChanged)) {
                    // Если расход остался оплаченным, но изменилась дата оплаты, способ оплаты, юр. лицо или количество позиций, обновляем транзакцию
                    $this->cashTransactions->replaceCashFlow($expense, $expense->getTotalSum(), $newPaymentDate, $newPaymentMethodId);
                }
            }

            // Установка payment_date, если is_paid изменено на 1 и payment_date не предоставлено
            if (!empty($data['is_paid'] == 1) && empty($data['payment_date']) && $expense->getOriginal('is_paid') != $data['is_paid']) {
                $data['payment_date'] = now()->toDateString(); // Форматирование даты на сегодняшний день
            }

            // Установка expense_type_id, если есть элементы
            if (isset($data['items']) && is_array($data['items']) && !empty($data['items'])) {
                $data['expense_type_id'] = $data['items'][0]['expense_type_id'];
            }

            return $expense;
        });
    }

    /**
     * Расчет общей суммы расхода.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return int
     */
    public function calculateTotalSum($query)
    {
        // Предполагается, что $query - это экземпляр запроса, который еще не был выполнен (не вызван метод get() или paginate()).
        return $query->with('items')->get()->sum(function ($expense) {
            return $expense->items->sum(function ($item) {
                return $item->amount * $item->price;
            });
        });
    }

    /**
     * Delete an expense.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);

        // Отменяем любые связанные транзакции
        $this->cashTransactions->rollbackCashFlow($expense);

        return $expense->delete();
    }

    /**
     * Загрузка файлов расхода.
     *
     * @param int $expenseId
     * @param mixed $files
     * @return Expense
     */
    public function uploadExpenseFiles($expenseId, $files)
    {
        $expense = Expense::findOrFail($expenseId);

        $fileData = $expense->saveExpenseFiles($files);

        // Обновление поля files в модели expense
        $currentFiles = $expense->files ?? [];
        $expense->files = array_merge($currentFiles, $fileData);
        $expense->save();

        return $expense;
    }

    /**
     * Загрузка файла расхода.
     *
     * @param int $expenseId
     * @param mixed $file
     * @return Expense
     */
    public function uploadExpenseFile($expenseId, $file)
    {
        $expense = Expense::findOrFail($expenseId);

        // Удаление предыдущего файла, если он существует
        $existingFile = json_decode($expense->files, true)['path'] ?? null;
        if ($existingFile) {
            Storage::disk('public')->delete($existingFile);
        }

        $fileData = $expense->saveExpenseFile($file);

        $expense->files = json_encode($fileData);
        $expense->save();

        return $expense;
    }

    /**
     * Загрузка файла счета.
     *
     * @param int $expenseId
     * @param mixed $file
     * @return Expense
     */
    public function uploadInvoiceFile($expenseId, $file)
    {
        $expense = Expense::findOrFail($expenseId);

        Log::info($file);

        // Удаление предыдущего файла, если он существует
        $existingFile = json_decode($expense->invoice_file, true)['path'] ?? null;
        if ($existingFile) {
            Storage::disk('public')->delete($existingFile);
        }

        $fileData = $expense->saveInvoiceFile($file);

        $expense->invoice_file = json_encode($fileData);
        $expense->save();

        return $expense;
    }

    /**
     * Конвертирует Хоз.расход в возврат ДС.
     *
     * @param array $data
     * @return MoneyRefundable
     */
    public function convertToMoneyRefund($data)
    {
        $moneyRefundable = new MoneyRefundable();
        $moneyRefundable->fill($data);
        $moneyRefundable->refundable_type = 'App\Models\V1\Expenses\Expense';
        $moneyRefundable->refundable_id = $data['id'];
        $moneyRefundable->created_at = now();
        $moneyRefundable->comment = 'Конвертация Хоз.расхода №' . $data['id'] . ' в возврат ДС';

        $expenseItems = Expense::find($data['id'])->items;
        $moneyRefundable->debt_sum = $expenseItems->sum('price');
        $moneyRefundable->save();

        return $moneyRefundable;
    }

    /**
     * Синхронизация Возврата ДС с Хоз.расходом.
     *
     * @param array $data
     * @return void
     */
    public function syncConvertedMoneyRefund($data)
    {
        $expense = Expense::find($data['id']);
        $moneyRefundable = $expense->refundable;
        $expenseItems = $expense->items;

        $moneyRefundable->legal_entity_id = $expense->legal_entity_id;
        $moneyRefundable->payment_method_id = $expense->payment_method_id;
        $moneyRefundable->contragent_id = $expense->contragent_id;
        $moneyRefundable->debt_sum = $expenseItems->sum('price');

        $moneyRefundable->save();
    }
}
