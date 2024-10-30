<?php

namespace App\Models\V1\Expenses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseSummaryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_summary_id',
        'expense_id',
    ];

    public function summary()
    {
        return $this->belongsTo(ExpenseSummary::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
