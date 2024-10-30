<?php

namespace App\Models\V1\Expenses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserStamps;

class ExpenseType extends Model
{
    use HasFactory;
    use UserStamps;

    protected $fillable = [
        'sort_order',
        'name',
        'is_receipt_optional',
        'created_by',
        'updated_by'
    ];

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }
}
