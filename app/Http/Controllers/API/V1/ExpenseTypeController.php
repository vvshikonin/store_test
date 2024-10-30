<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Entities\Expenses\ExpenseTypeService;
use App\Http\Resources\V1\Expense\ExpenseTypeResource;
use App\Models\V1\Expenses\ExpenseType;

class ExpenseTypeController extends Controller
{
    protected $expenseTypeService;

    public function __construct(ExpenseTypeService $expenseTypeService)
    {
        $this->expenseTypeService = $expenseTypeService;
    }

    public function index()
    {
        $expenseTypes = $this->expenseTypeService->getAllExpenseTypes();
        return ExpenseTypeResource::collection($expenseTypes);
    }

    public function show($id)
    {
        $expenseType = $this->expenseTypeService->getExpenseTypeById($id);
        return new ExpenseTypeResource($expenseType);
    }

    public function update(Request $request, $id)
    {
        $expenseType = $this->expenseTypeService->updateExpenseType($id, $request->all());
        return new ExpenseTypeResource($expenseType);
    }

    public function destroy($id)
    {
        $this->expenseTypeService->deleteExpenseType($id);
        return response()->json(null, 204);
    }

    public function store(Request $request)
    {
        $expenseType = $this->expenseTypeService->createExpenseType($request->all());
        return new ExpenseTypeResource($expenseType);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'updatedOrder' => 'required|array',
            'updatedOrder.*.id' => 'required|exists:expense_types,id',
            'updatedOrder.*.sort_order' => 'required|integer',
        ]);

        $updatedOrder = $request->input('updatedOrder');
        if ($updatedOrder) {
            foreach ($updatedOrder as $item) {
                $expenseType = ExpenseType::find($item['id']);
                if ($expenseType) {
                    $expenseType->sort_order = $item['sort_order'];
                    $expenseType->save();
                }
            }
        } else {
            // Обработка случая, когда updatedOrder не передан или передан некорректно
            return response()->json(['error' => 'Неверный формат данных для updatedOrder'], 400);
        }

        return response()->json(['message' => 'Порядок типов расходов успешно обновлен.']);
    }
}
