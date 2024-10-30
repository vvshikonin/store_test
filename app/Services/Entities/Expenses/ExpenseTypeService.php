<?php

namespace App\Services\Entities\Expenses;

use App\Models\V1\Expenses\ExpenseType;

class ExpenseTypeService
{
    public function getAllExpenseTypes()
    {
        return ExpenseType::orderBy('sort_order', 'asc')->get();
    }

    public function getExpenseTypeById($id)
    {
        return ExpenseType::findOrFail($id);
    }

    public function updateExpenseType($id, array $data)
    {
        $expenseType = ExpenseType::findOrFail($id);
        $expenseType->update($data);
        return $expenseType;
    }

    public function deleteExpenseType($id)
    {
        $expenseType = ExpenseType::findOrFail($id);
        $expenseType->delete();
    }

    public function createExpenseType(array $data)
    {
        return ExpenseType::create($data);
    }
}
