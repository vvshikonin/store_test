<?php

namespace App\Exports\Expenses;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Services\Entities\Expenses\ExpenseTypeService;
use App\Models\V1\Expenses\ExpenseItem;
use App\Models\V1\Expenses\ExpenseType;

class SortedExpenseItemsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    private $expenseTypeService;
    private $requestParams; // Для хранения параметров запроса

    public function __construct(ExpenseTypeService $expenseTypeService, $requestParams = [])
    {
        $this->expenseTypeService = $expenseTypeService;
        $this->requestParams = $requestParams; // Сохраняем параметры запроса
    }

    public function collection()
    {
        $expenseTypes = $this->expenseTypeService->getAllExpenseTypes($this->requestParams);
        $sortedItems = collect();

        foreach ($expenseTypes as $type) {
            $items = ExpenseItem::where('expense_type_id', $type->id)->get();

            if ($items->isEmpty()) {
                // Добавляем пустую запись для типов расходов без элементов
                $sortedItems->push((object)[
                    'expense_type_name' => $type->name,
                    'expense_type_id' => $type->id,
                    'item_name' => null,
                    'amount' => null,
                    'price' => null
                ]);
            } else {
                foreach ($items as $item) {
                    $item->expense_type_name = $type->name;
                    $sortedItems->push($item);
                }
            }
        }

        return $sortedItems;
    }

    public function headings(): array
    {
        return [
            'Тип расходов',
            'ID Типа',
            'Название элемента',
            'Количество',
            'Цена'
        ];
    }

    public function map($row): array
    {
        return [
            $row->expense_type_name,
            $row->expense_type_id,
            $row->item_name ?? '---',
            $row->amount ?? '---',
            $row->price ?? '---'
        ];
    }
}
