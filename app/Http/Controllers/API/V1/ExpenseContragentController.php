<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Expenses\ExpenseContragent;
use Illuminate\Http\Request;

class ExpenseContragentController extends Controller
{
    public function index(Request $request)
    {    
        $query = ExpenseContragent::query();
    
        if ($request->has('only_active') && $request->only_active === 'true') {
            $query->where('is_active', true);
        }
    
        $expenseContragents = $query->get();
    
        $expenseContragents->each(function ($contragent) {
            $contragent->append('last_payment_date');
        });
    
        return response()->json($expenseContragents);
    }    

    public function show($id)
    {
        return ExpenseContragent::findOrFail($id);
    }

    public function store(Request $request)
    {
        return ExpenseContragent::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $expenseContragent = ExpenseContragent::findOrFail($id);
        $expenseContragent->update($request->all());
        return $expenseContragent;
    }

    // public function updateExpenseTypes(Request $request, $id)
    // {
    //     $expenseContragent = ExpenseContragent::findOrFail($id);
    //     $expenseContragent->expenseTypes()->sync($request->expenseTypes);
    //     return $expenseContragent;
    // }

    public function destroy($id)
    {
        $expenseContragent = ExpenseContragent::findOrFail($id);
        $expenseContragent->delete();
        return response()->json(null, 204);
    }
}
