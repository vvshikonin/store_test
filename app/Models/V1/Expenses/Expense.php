<?php

namespace App\Models\V1\Expenses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserStamps;
use App\Traits\BaseFilter;

use App\Models\V1\PaymentMethod;
use App\Models\V1\LegalEntity;
use App\Models\V1\MoneyRefundable;
use App\Models\V1\Expenses\ExpenseContragent;
use Illuminate\Support\Facades\Log;

class Expense extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UserStamps;
    use BaseFilter;

    protected $fillable = [
        'expense_type_id',
        'comment',
        'contragent_id',
        'payment_date',
        'legal_entity_id',
        'payment_method_id',
        'is_paid',
        'is_need_to_complete',
        'is_edo',
        'accounting_month',
        'accounting_year',
        'files',
        'invoice_file',
        'updated_by'
    ];

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function contragent()
    {
        return $this->belongsTo(ExpenseContragent::class, 'contragent_id');
    }

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function moneyRefundable()
    {
        return $this->morphOne(MoneyRefundable::class, 'refundable');
    }

    /**
     * Сохраняет файлы на сервере и записывает пути в массив.
     *
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null $file
     */
    public function saveExpenseFiles($files)
    {
        $fileData = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            $fileName = hash('sha256', $file->get());
            $path = $file->storeAs('expenses', $fileName . '.' . $extension, 'public');

            $fileData[$path] = $originalName;
        }

        return $fileData;
    }

    public function saveExpenseFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $fileName = hash('sha256', $file->get());
        $path = $file->storeAs('expenses', $fileName . '.' . $extension, 'public');

        return ['path' => $path, 'name' => $originalName];
    }

    public function saveInvoiceFiles($files)
    {
        $fileData = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            $fileName = hash('sha256', $file->get());
            $path = $file->storeAs('expenses', $fileName . '.' . $extension, 'public');

            $fileData[$path] = $originalName;
        }

        return $fileData;
    }

    public function saveInvoiceFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $fileName = hash('sha256', $file->get());
        $path = $file->storeAs('expenses', $fileName . '.' . $extension, 'public');

        return ['path' => $path, 'name' => $originalName];
    }

    /**
     * Фильтр по дате создания.
     */
    public function scopeCreatedAtFilter($query, $dates)
    {
        if ($dates !== null) {
            $query->betweenDateTimeFilter('expenses.created_at', $dates);
        }
    }

    /**
     * Фильтр по дате оплаты.
     */
    public function scopePaymentDateFilter($query, $dates)
    {
        if ($dates !== null) {
            $query->betweenDateTimeFilter('expenses.payment_date', $dates);
        }
    }

    /**
     * Фильтр по комментарию.
     */
    public function scopeCommentFilter($query, $comment)
    {
        if (!empty($comment)) {
            return $query->whereLike('comment', $comment);
        }
    }

    /**
     * Фильтр по типам расходов через связанные элементы расходов.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $expenseTypeIds Массив ID типов расходов.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpenseTypesFilter($query, $expenseTypeIds)
    {
        if (!empty($expenseTypeIds)) {
            $query->whereHas('items', function ($query) use ($expenseTypeIds) {
                $query->whereIn('expense_type_id', $expenseTypeIds);
            });
        }
    }

    /**
     * Фильтр по юр.лицу.
     */
    public function scopeLegalEntityFilter($query, $legalEntityIds)
    {
        if (!empty($legalEntityIds)) {
            $query->whereIn('legal_entity_id', $legalEntityIds);
        }
    }

    /**
     * Фильтр по способу оплаты.
     */
    public function scopePaymentMethodFilter($query, $paymentMethodIds)
    {
        if (!empty($paymentMethodIds)) {
            $query->whereIn('payment_method_id', $paymentMethodIds);
        }
    }

    /**
     * Фильтр по статусу оплаты.
     */
    public function scopePaymentStatusFilter($query, $paymentStatus)
    {
        if (!is_null($paymentStatus)) {
            $query->where('is_paid', $paymentStatus);
        }
    }

    /**
     * Фильтр по общей сумме расхода
     */
    public function scopeSumFilter($query, $sums)
    {
        $sums = json_decode($sums, true);

        if (!is_null($sums)) {
            $subQuery = Expense::selectRaw('expenses.id, SUM(expense_items.amount * expense_items.price) as total')
                ->join('expense_items', 'expenses.id', '=', 'expense_items.expense_id')
                ->groupBy('expenses.id');

            // Добавление условий в зависимости от переданных параметров
            if (isset($sums['equal'])) {
                $subQuery->havingRaw('total = ?', [$sums['equal']]);
            } elseif (isset($sums['notEqual'])) {
                $subQuery->havingRaw('total != ?', [$sums['notEqual']]);
            } elseif (isset($sums['start']) && isset($sums['end'])) {
                $subQuery->havingRaw('total BETWEEN ? AND ?', [$sums['start'], $sums['end']]);
            }

            // Получаем идентификаторы из подзапроса
            $expenseIds = $subQuery->pluck('id');

            // Фильтруем основной запрос по идентификаторам, которые нашли
            $query->whereIn('id', $expenseIds);
        }

        return $query;
    }

    /**
     * Фильтр по контрагенту
     */
    public function scopeContragentFilter($query, $ContragentIds)
    {
        if (!empty($ContragentIds)) {
            $query->whereIn('contragent_id', $ContragentIds);
        }
    }

    /**
     * Фильтр по создателю (ответственному)
     */
    public function scopeCreatorFilter($query, $creatorId)
    {
        if ($creatorId) {
            $query->where('created_by', $creatorId);
        }
    }

    /**
     * Фильтр по создателям (ответственным)
     */
    public function scopeCreatorsFilter($query, $creatorIds)
    {
        if ($creatorIds) {
            $query->whereIn('created_by', $creatorIds);
        }
    }

    /**
     * Фильтр по месяцам периода.
     * Этот метод позволяет фильтровать записи по нескольким месяцам одновременно,
     * включая записи, где accounting_month равен null.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param array|null $months Массив месяцев для фильтрации или null.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountingMonthsFilter($query, $months)
    {
        Log::info('AccountingMonthsFilter input: ' . print_r($months, true));
        Log::info('AccountingMonthsFilter input type: ' . gettype($months));
        Log::info('First element type: ' . gettype($months[0]));

        // Преобразуем строку "null" в действительное значение null
        if (is_array($months) && (empty($months) || $months[0] === null || $months[0] === 'null')) {
            Log::info('NULL AccountingMonthsFilter: ' . print_r($months, true));
            $query->whereNull('accounting_month');
        } elseif (is_array($months) && $months !== null) {
            Log::info('AccountingMonthsFilter: ' . print_r($months, true));
            $query->whereIn('accounting_month', $months);
        }

        return $query;
    }

    /**
     * Фильтр по годам периода.
     * Этот метод позволяет фильтровать записи по нескольким годам одновременно,
     * включая записи, где accounting_year равен null.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param array|null $years Массив годов для фильтрации или null.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountingYearsFilter($query, $years)
    {
        Log::info('AccountingYearsFilter input: ' . print_r($years, true));
        Log::info('AccountingYearsFilter input type: ' . gettype($years));
        Log::info('First element type: ' . gettype($years[0]));

        // Преобразуем строку "null" в действительное значение null
        if (is_array($years) && (empty($years) || $years[0] === null || $years[0] === 'null')) {
            Log::info('NULL AccountingYearsFilter: ' . print_r($years, true));
            $query->whereNull('accounting_year');
        } elseif (is_array($years) && $years !== null) {
            Log::info('AccountingYearsFilter: ' . print_r($years, true));
            $query->whereIn('accounting_year', $years);
        }

        return $query;
    }

    /**
     * Фильтр по номеру расхода
     */
    public function scopeIdFilter($query, $id)
    {
        if ($id) {
            $query->where('id', $id);
        }
    }

    /**
     * Фильтр по наличию чека в ЭДО.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeIsEdo($query, $isEdo)
    {
        if ($isEdo == "1") {
            $query->where('expenses.is_edo', true);
        } else if ($isEdo == "0") {
            $query->where('expenses.is_edo', false);
        }
    }

    /**
     * Фильтр по наличию файла счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeHasInvoiceFile($query, $hasFile)
    {
        if ($hasFile == "1") {
            $query->whereNotNull('expenses.invoice_file')->where('expenses.invoice_file', '!=', '[]');
        } else if ($hasFile == "0") {
            $query->where(function ($query) {
                $query->whereNull('expenses.invoice_file')->orWhere('expenses.invoice_file', '=', '[]');
            });
        }
    }

    /**
     * Фильтр по наличию файла чека.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeHasReceiptFile($query, $hasFile)
    {
        if ($hasFile == "1") {
            $query->whereNotNull('expenses.files')->where('expenses.files', '!=', '[]');
        } else if ($hasFile == "0") {
            $query->where(function ($query) {
                $query->whereNull('expenses.files')->orWhere('expenses.files', '=', '[]');
            });
        }
    }

    /**
     * Вычисляет общую сумму расходов для текущего экземпляра Expense.
     *
     * @return float
     */
    public function getTotalSum()
    {
        // Если у расхода уже есть загруженные items, используйте их,
        // иначе загрузите связанные items из базы данных.
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        // Используйте collection метод sum для подсчета общей суммы
        return $items->sum(function ($item) {
            return $item->amount * $item->price;
        });
    }
}
