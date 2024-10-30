<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Models\V1\MoneyRefundable;
use App\Casts\YesNoCast;
use App\Casts\NumberFormatCast;
use App\Casts\MoneyRefundables\StatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MoneyRefundables\MoneyRefundableResource;
use App\Http\Resources\V1\MoneyRefundables\MoneyRefundableCollection;
use App\Http\Requests\MoneyRefundable\IndexRequest;
use App\Http\Requests\MoneyRefundable\UpdateRequest;
use App\Exports\MoneyRefunds\MoneyRefundsExport;
use App\Exports\MoneyRefunds\MoneyRefundIncomesExport;
use App\Models\MoneyRefundIncome;
use App\Services\Entities\MoneyRefundService;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\V1\Expenses\Expense;
use App\Models\V1\Expenses\ExpenseItem;

class MoneyRefundableController extends Controller
{
    /**
     * Возвращает список возвратов ДС
     * 
     * @param IndexRequest $request
     * @return MoneyRefundableCollection
     */
    public function index(IndexRequest $request)
    {
        $moneyRefundables = MoneyRefundable::with([
            'contractor:id,name',
            'contragent:id,name',
            'legalEntity:id,name',
            'paymentMethod:id,name',
            'refundable',
        ])
            ->orderBy($request->sort_field ?: 'id', $request->sort_type ?: 'desc')
            ->applyFilters($request->all());

        $total_refund_sum = $moneyRefundables->sum(DB::raw('refund_sum_money + refund_sum_products'));
        $total_debt_sum = $moneyRefundables->sum('debt_sum');

        $moneyRefundables = $moneyRefundables->paginate($request?->per_page);

        foreach ($moneyRefundables as $refundable) {
            if ($refundable?->refundable && method_exists($refundable->refundable, 'order')) {
                $refundable->load('refundable.order');
            }
        }

        return new MoneyRefundableCollection(
            $moneyRefundables,
            $total_refund_sum,
            $total_debt_sum
        );
    }

    /**
     * Возвращает возврат ДС по переданному в запросе ID.
     * 
     * @param MoneyRefundable $moneyRefundable
     * @return MoneyRefundableResource
     */
    public function show(MoneyRefundable $moneyRefundable)
    {
        $this->authorize('view', $moneyRefundable);

        $moneyRefundable->load([
            'contractor:id,name,is_main_contractor',
            'contragent:id,name',
            'legalEntity:id,name',
            'paymentMethod:id,name',
            'incomes',
            'creator',
            'updater'
        ]);

        return new MoneyRefundableResource($moneyRefundable);
    }

    public function store(UpdateRequest $request)
    {
        $refundData = $request->all();
        $refundData['refundable_type'] = MoneyRefundable::class;
        $refundData['refundable_id'] = 0;
        $refund = MoneyRefundable::create($refundData);
        $refund->created_at = $request->created_at;
        $refund->save();
        return $refund;
    }

    public function update(UpdateRequest $request, $id, MoneyRefundService $refundService)
    {
        $refund = MoneyRefundable::find($id);
        $this->authorize('update', $refund);
        $refund = $refundService->update($refund, $request->all());

        return $refund;
    }

    public function uploadDocFile($id, Request $request)
    {
        $refund = MoneyRefundable::findOrFail($id);
        $this->authorize('update', $refund);

        if ($request->has('refund_doc_file'))
            $refund->storeFile($request->file('refund_doc_file'));

        return $refund;
    }

    /**
     * Создаёт Excel выгрузку по возвратам ДС
     *
     * @param  \App\Requests\MoneyRefundable\IndexRequest  $request
     * @return \Maatwebsite\Excel\Facades\Excel
     */
    public function export(IndexRequest $request)
    {
        $moneyRefundables = MoneyRefundable::with([
            'contractor:id,name',
            'contragent:id,name',
            'legalEntity:id,name',
            'paymentMethod:id,name',
            'refundable'
        ])->withCasts([
            'debt_sum' => NumberFormatCast::class,
            'refund_sum_money' => NumberFormatCast::class,
            'status' => StatusCast::class,
            'created_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved' => YesNoCast::class
        ])->orderBy($request->sort_field ?: 'id', $request->sort_type ?: 'desc')
            ->applyFilters($request->all())
            ->get();

        return Excel::download(new MoneyRefundsExport($moneyRefundables), 'money_refunds.xlsx');
    }

    /**
     * Создаёт Excel выгрузку по возвратам ДС
     *
     * @param  \App\Requests\MoneyRefundable\IndexRequest  $request
     * @return \Maatwebsite\Excel\Facades\Excel
     */
    public function exportIncomes(IndexRequest $request)
    {
        $moneyRefundables_ids = MoneyRefundable::select('money_refundables.id')
            ->applyFilters($request->all());

        $incomes = MoneyRefundIncome::with([
            'moneyRefund' => function ($query) {
                $query->withCasts([
                    'debt_sum' => NumberFormatCast::class,
                    'refund_sum_money' => NumberFormatCast::class,
                    'status' => StatusCast::class,
                    'approved' => YesNoCast::class
                ]);
            },
            'moneyRefund.contractor:id,name',
            'moneyRefund.contragent:id,name',
            'moneyRefund.legalEntity:id,name',
            'moneyRefund.paymentMethod:id,name',
            'moneyRefund.refundable',
        ])->withCasts([
            'created_at' => 'datetime',
            'completed_at' => 'datetime',
        ])->whereIn('money_refundable_id', $moneyRefundables_ids)->get();

        return Excel::download(new MoneyRefundIncomesExport($incomes), 'money_refunds.xlsx');
    }

    public function convertToExpense($id, MoneyRefundService $moneyRefundService)
    {
        try {
            // Получаем объект MoneyRefundable по ID
            $moneyRefund = MoneyRefundable::findOrFail($id);

            // Загружаем связанные данные
            $moneyRefund->load('refundable');

            // Выполняем конвертацию
            $expense = $moneyRefundService->convertToExpense($moneyRefund);

            // Обновляем поле converted_to_expense_at
            $moneyRefund->converted_to_expense_at = now();
            $moneyRefund->converted_to_expense_id = $expense->id;
            $moneyRefund->save();

            return response()->json(['success' => true, 'expenseId' => $expense->id]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при конвертации MoneyRefundable: ' . $e->getMessage());
            \Log::error('MoneyRefundable ID: ' . $id);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
