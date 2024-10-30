<?php

namespace App\Models\V1\Expenses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UserStamps;
use App\Models\V1\Expenses\Expense;
use App\Models\V1\Expenses\ExpenseType;
use App\Models\User;
class ExpenseContragent extends Model
{
    use HasFactory;
    use UserStamps;

    protected $fillable = [
        'name',
        'is_receipt_optional',
        'is_period_coincides',
        'user_id',
        'regular_payment',
        'created_by',
        'related_expense_types',
        'special_conditions',
        'is_active',
        'updated_by'
    ];

    protected $casts = [
        'related_expense_types' => 'array',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'contragent_id');
    }

    public function expenseTypes()
    {
        return $this->hasMany(ExpenseType::class);
    }

    public function getLastPaymentDateAttribute()
    {
        $lastExpense = $this->expenses()->orderBy('payment_date', 'desc')->first();
        return $lastExpense ? $lastExpense->payment_date : null;
    }

    // Связь с user через user_id
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
