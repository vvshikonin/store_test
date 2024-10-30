<?php

namespace App\Models\V1\Expenses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserStamps;

class ExpenseItem extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UserStamps;

    protected $fillable = [
        'expense_type_id',
        'custom_name',
        'name',
        'amount',
        'price'
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function type()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }
}
