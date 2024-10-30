<?php

namespace App\Models\V1\Expenses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BaseFilter;
use Illuminate\Support\Facades\Log;

class ExpenseSummary extends Model
{
    use HasFactory;
    use BaseFilter;

    protected $fillable = [
        'accounting_month',
        'accounting_year',
        'total_income',
        'total_expenses',
        'financial_result',
    ];

    /**
     * Связь с элементами расходов.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(ExpenseSummaryItem::class);
    }

    /**
     * Фильтр по месяцу периода.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param string $month Месяц для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountingMonthFilter($query, $month)
    {
        // Log::info('FilterByAccountingMonth: ' . $month);
        if (!empty($month)) {
            $query->where('accounting_month', '=', $month);
        }

        return $query;
    }

    /**
     * Фильтр по году периода.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param string $year Год для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountingYearFilter($query, $year)
    {
        // Log::info('FilterByAccountingYear: ' . $year);
        if (!empty($year)) {
            $query->where('accounting_year', '=', $year);
        }

        return $query;
    }
    /**
     * Фильтр по месяцам периода.
     * Этот метод позволяет фильтровать записи по нескольким месяцам одновременно.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param array $months Массив месяцев для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountingMonthsFilter($query, $months)
    {
        if (!empty($months)) {
            $query->whereIn('accounting_month', $months);
        }

        return $query;
    }

    /**
     * Фильтр по годам периода.
     * Этот метод позволяет фильтровать записи по нескольким годам одновременно.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param array $years Массив годов для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountingYearsFilter($query, $years)
    {
        if (!empty($years)) {
            $query->whereIn('accounting_year', $years);
        }

        return $query;
    }

    /**
     * Фильтр по юр.лицам.
     * Этот метод позволяет фильтровать записи по юр.лицам.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Экземпляр построителя запросов.
     * @param array $legalEntities Массив юр.лиц для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLegalEntityFilter($query, $legalEntities)
    {
        if (!empty($legalEntities)) {
            $query->whereHas('items', function ($q) use ($legalEntities) {
                $q->whereHas('expense', function ($q) use ($legalEntities) {
                    $q->whereIn('legal_entity_id', $legalEntities);
                });
            });
        }

        return $query;
    }
}
