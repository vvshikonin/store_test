<?php

namespace App\Models\V1;

use App\Models\MoneyRefundIncome;
use App\Models\V1\Expenses\Expense;
use App\Models\V1\Expenses\ExpenseContragent;
use App\Traits\BaseFilter;
use App\Traits\StringHandle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use App\Traits\UserStamps;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|MoneyRefundable query()
 * @method static \Illuminate\Database\Eloquent\Builder|MoneyRefundable select()
 * @method static \Illuminate\Database\Eloquent\Builder|MoneyRefundable with()
 * @method static \Illuminate\Database\Eloquent\Builder|MoneyRefundable orderBy()
 *
 * @property int $id ID возврата ДС.
 * @property int $refundable_id ID полиморфной связи.
 * @property string $refundable_type Тип полиморфной связи.
 * @property int $contractor_id ID поставщика.
 * @property int $payment_method_id ID способа оплаты.
 * @property int $legal_entity_id ID юр.лица.
 * @property int $status Статус возврата.
 * @property string $reason Причина возврата(только для созданных вручную).
 * @property string $comment Комментарий.
 * @property float $debt_sum Сумма долга поставщика.
 * @property float $refund_sum_money Сумма возврата в деньгах.
 * @property string $completed_at Время завершения.
 *
 * @property float $remainingDebt остаточная сумма долга поставщика.
 *
 * @property mixed $refundable Полиморфно связанная модель.
 * @property Contractor $contractor Связанный поставщик.
 * @property LegalEntity $legalEntity Связанное юр.лицо.
 * @property PaymentMethod $paymentMethod Связанных способ оплаты.
 * @property Expense $expense Связанный расход.
 * @property ExpenseContragent $contragent Связанный контрагент.
 *
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable typeFilter(string $type) Фильтр по типу возврата(связанной модели).
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable reasonFilter(string $reason) Фильтр по причине возврата(номеру счёта, заказа, тексту).
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable contractorsFilter(int[] $contractorsIDs) Фильтр по поставщику.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable sumFilter(array $sum) Фильтр по сумме.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable refundSumFilter(array $sum) Фильтр по сумме возврата.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable legalEntityFilter(int $ID) Фильтр по юр.лицу.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable paymentMethodFilter(int $ID) Фильтр по способу оплаты.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable statusFilter(int $statusCode) Фильтр по статуса.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable approvedFilter(int $approveStatusCode) Фильтр по подтверждению оплаты.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable createdAtFilter(array $dates) Фильтр по дате создания.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable completedAtFilter(array $dates) Фильтр по дате завершения.
 * @method \Illuminate\Database\Eloquent\Builder|MoneyRefundable applyFilters(array $filters) Применяет фильтры по переданному массиву.
 */
class MoneyRefundable extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BaseFilter;
    use StringHandle;
    use UserStamps;

    protected $fillable = [
        'contractor_id',
        'contragent_id',
        'payment_method_id',
        'legal_entity_id',
        'status',
        'reason',
        'comment',
        'debt_sum',
        'refund_sum_money',
        'completed_at',
        'refundable_type',
        'refundable_id',
        'approved',
        'deduction_made',
        'converted_to_expense_at',
        'converted_to_expense_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'converted_to_expense_at' => 'datetime',
    ];

    /**
     * Отношение MorphTo.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function refundable()
    {
        return $this->morphTo();
    }

    /**
     * Отношение BelongsTo c Contractor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Отношение BelongsTo c Contragent.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contragent()
    {
        return $this->belongsTo(ExpenseContragent::class);
    }

    /**
     * Отношение BelongsTo c LegalEntity.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    /**
     * Отношение BelongsTo c PaymentMethod.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'converted_to_expense_id');
    }

    public function incomes()
    {
        return $this->hasMany(MoneyRefundIncome::class);
    }

    /**
     * Сумма поступления возвратов ДС.
     */
    public function incomesSum()
    {
        return $this->incomes()->sum('sum');
    }

    /**
     * Фильтрует MoneyRefundable по причине возврата.
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $reason
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReasonFilter($query, $reason)
    {
        if ($reason !== null) {
            $invoicesIDs = Invoice::select('id')->whereLike('number', $reason);
            $contractorRefundsIDs = ContractorRefund::select('id')->whereIn('id', $invoicesIDs);

            $ordersIDs = Order::select('id')->whereNumber('number', $reason);
            $defectsIDs = Defect::select('id')->whereIn('order_id', $ordersIDs);
            $productRefundsIDs = ProductRefund::select('id')->whereIn('order_id', $ordersIDs);

            $query->where(function ($query) use ($invoicesIDs) {
                $query->where('refundable_type', Invoice::class)->whereIn('refundable_id', $invoicesIDs);
            })->orWhere(function ($query) use ($contractorRefundsIDs) {
                $query->where('refundable_type', ContractorRefund::class)->whereIn('refundable_id', $contractorRefundsIDs);
            })->orWhere(function ($query) use ($defectsIDs) {
                $query->where('refundable_type', Defect::class)->whereIn('refundable_id', $defectsIDs);
            })->orWhere(function ($query) use ($productRefundsIDs) {
                $query->where('refundable_type', ProductRefund::class)->whereIn('refundable_id', $productRefundsIDs);
            })->orWhereLike('reason', $reason);
        }
    }

    /**
     * Фильтрует MoneyRefundable по связанной модели.
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTypeFilter($query, $type)
    {
        if ($type !== null)
            $query->where(function ($query) use ($type) {
                $query->where('money_refundables.refundable_type', $type)
                    ->orWhere('money_refundables.reason', $type);
            });
    }

    /**
     * Фильтрует MoneyRefundable по поставщикам.
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param integer[]|null $contractorsIDs ID поставщиков в БД
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorsFilter($query, $contractorsIDs)
    {
        if ($contractorsIDs !== null)
            $query->whereIn('money_refundables.contractor_id', $contractorsIDs);
    }

    /**
     * Фильтрует по основному или дополнительному поставщику.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool|null $isMainContractor Флаг, указывающий, является ли поставщик основным.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorsTypeFilter($query, $isMainContractor)
    {
        if ($isMainContractor !== null) {
            $query->whereHas('contractor', function ($query) use ($isMainContractor) {
                $query->where('is_main_contractor', $isMainContractor);
            });
        }
    }

    /**
     * Фильтрует MoneyRefundable по контрагентам.
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param integer[]|null $contragentsIDs ID контрагентов в БД
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContragentsFilter($query, $contragentsIDs)
    {
        if ($contragentsIDs !== null)
            $query->whereIn('money_refundables.contragent_id', $contragentsIDs);
    }

    /**
     * Фильтрует MoneyRefundable по сумме возврата
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $sums `json` строка с ключами: `start`, `end`, `equal`, `notEqual`
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSumFilter($query, $sums)
    {
        if ($sums !== null)
            $query->betweenFilter('money_refundables.debt_sum', $sums);
    }

    /**
     * Фильтрует MoneyRefundable по фактическому возврату
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $actual_refunds `json` строка с ключами: `start`, `end`, `equal`, `notEqual`
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeRefundSumFilter($query, $refund_sum_money)
    // {
    //     if ($refund_sum_money !== null)
    //         $query->betweenFilter('money_refundables.refund_sum_money' + 'money_refundables.refund_sum_products', $refund_sum_money);
    // }
    public function scopeRefundSumFilter($query, $actualRefunds)
    {
        if ($actualRefunds !== null) {
            $data = json_decode($actualRefunds, true);

            $start = $data['start'] ?? null;
            $end = $data['end'] ?? null;
            $equal = $data['equal'] ?? null;
            $notEqual = $data['notEqual'] ?? null;

            if ($start !== null && $end !== null) {
                $query->whereRaw('(money_refundables.refund_sum_money + money_refundables.refund_sum_products) BETWEEN ? AND ?', [$start, $end]);
            } elseif ($start !== null) {
                $query->whereRaw('(money_refundables.refund_sum_money + money_refundables.refund_sum_products) >= ?', [$start]);
            } elseif ($end !== null) {
                $query->whereRaw('(money_refundables.refund_sum_money + money_refundables.refund_sum_products) <= ?', [$end]);
            } elseif ($equal !== null) {
                $query->whereRaw('(money_refundables.refund_sum_money + money_refundables.refund_sum_products) = ?', [$equal]);
            } elseif ($notEqual !== null) {
                $query->whereRaw('(money_refundables.refund_sum_money + money_refundables.refund_sum_products) != ?', [$notEqual]);
            }
        }
    }

    /**
     * Фильтрует MoneyRefundable по юр.лицу
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param integer|null $legalEntityID ID юридического лица
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLegalEntityFilter($query, $legalEntityID)
    {
        if ($legalEntityID !== null)
            $query->where('money_refundables.legal_entity_id', $legalEntityID);
    }

    /**
     * Фильтрует MoneyRefundable по способу оплаты
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param integer|null $paymentMethodID ID способа оплаты
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaymentMethodFilter($query, $paymentMethodID)
    {
        if ($paymentMethodID !== null)
            $query->where('money_refundables.payment_method_id', $paymentMethodID);
    }

    /**
     * Фильтрует MoneyRefundable по статусу
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param boolean|null $status Статус возврата
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatusFilter($query, $status)
    {
        if ($status !== null) {
            $query->where('money_refundables.status', $status);
        }
    }

    /**
     * Фильтрует MoneyRefundable по id
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param integer|null $id id возврата дс
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where('money_refundables.id', $id);
        }
    }

    /**
     * Фильтрует MoneyRefundable по подтверждению директором
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param boolean|null $approved Подтверждение директором
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApprovedFilter($query, $approved)
    {
        if ($approved !== null) {
            $query->where('money_refundables.approved', $approved);
        }
    }

    /**
     * Фильтрует MoneyRefundable по дате создания
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $created_at_dates `json` строка с ключами: `start`, `end`, `equal`, `notEqual`
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedAtFilter($query, $created_at_dates)
    {
        if ($created_at_dates !== null) {
            $query->betweenDateTimeFilter('money_refundables.created_at', $created_at_dates);
        }
    }

    /**
     * Фильтрует MoneyRefundable по дате возврата
     *
     * @param mixed \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $completed_at_dates `json` строка с ключами: `start`, `end`, `equal`, `notEqual`
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompletedAtFilter($query, $completed_at_dates)
    {
        if ($completed_at_dates !== null) {
            $query->betweenDateTimeFilter('money_refundables.completed_at', $completed_at_dates);
        }
    }

    /**
     * Обрабатывает загрузку файла на сервер и сохраняет ссылку в БД.
     *
     * @param mixed $file
     */

    public function storeFile($refundFile)
    {
        try {
            $extension = $refundFile->getClientOriginalExtension();
            $fileName = hash('sha256', $refundFile->get());
            $path = $refundFile->storeAs('money_refunds', $fileName . '.' . $extension, 'public');
            $this->refund_doc_file = $path;
            $this->update(['refund_doc_file' => $path]);
        } catch (\Exception $e) {
            Log::error('Ошибка при сохранении файла: ' . $e->getMessage());
        }
    }

    /**
     * Возвращает остаточную сумму долга по возврату ДС.
     */
    public function getRemainingDebtAttribute()
    {
        return $this->debt_sum - ($this->refund_sum_money + $this->refund_sum_products);
    }
}
