<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\FinancialControl;
use Illuminate\Http\Request;
use App\Http\Resources\V1\FinancialControl\FinancialControlCollection;
use App\Models\V1\CashTransactionable;
use Illuminate\Support\Facades\DB;
use App\Exports\FinancialControlExport;
use App\Exports\FinancialControllAllTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Casts\TransactionableTypeName;
use App\Imports\FinancialControlImport;
use App\Services\EntityNameToIdMatcher;
use Illuminate\Support\Facades\Log;

class FinancialControlController extends Controller
{
    public function makeIndexRequest(Request $request)
    {
        // Группируем транзакции по способу оплаты и дате
        // и добаляем поля manual_sum для объеденения с фин.контролями.
        $transactions = CashTransactionable::select('payment_method_id')
            ->selectRaw('DATE(created_at) as payment_date')
            ->selectRaw('SUM(CASE WHEN type = "In" THEN -sum ELSE sum END) as auto_sum')
            ->selectRaw('0 as manual_sum')
            ->groupBy('payment_method_id', DB::raw('DATE(created_at)'));
        // Группируем фин.контроли по способу оплаты и дате
        // и добавляем поле auto_sum для объеденения с транзакциями.
        $controls = FinancialControl::select('payment_method_id', 'payment_date')
            ->selectRaw('0 as auto_sum')
            ->selectRaw('SUM(CASE WHEN type = "In" THEN -sum ELSE sum END) as manual_sum')
            ->groupBy('payment_method_id', 'payment_date');
        // Объеденяем сгруппированные транзакции и фин.контроли,
        // и повторно группируем по способу оплаты и дате,
        // что бы объединить задублированные сочетания.
        $combined = FinancialControl::query()
            ->fromSub($controls->unionAll($transactions), 'combined')
            ->select('payment_method_id', 'payment_date')
            ->selectRaw('SUM(auto_sum) as auto_sum')
            ->selectRaw('SUM(manual_sum) as manual_sum')
            ->selectRaw('SUM(auto_sum) - SUM(manual_sum) as difference')
            ->groupBy('payment_method_id', 'payment_date');

        // Присоединяем таблицы способов оплаты и юр.лиц
        // и забираем их имена из присоединённых таблиц.
        $result = $combined
            ->addSelect('payment_methods.name as payment_method_name')
            ->addSelect('legal_entities.id as legal_entity_id')
            ->addSelect('legal_entities.name as legal_entity_name')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'combined.payment_method_id')
            ->leftJoin('legal_entities', 'legal_entities.id', '=', 'payment_methods.legal_entity_id');

        $result->orderBy(
            $request->get('sort_field') ?? 'id',
            $request->get('sort_type') ?? 'desc'
        );

        return $result->applyFilters($request);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', FinancialControl::class);

        $totalManualSum = FinancialControl::query()
            ->fromSub($this->makeIndexRequest($request), 'sub')
            ->sum('manual_sum');

        $totalAutoSum = FinancialControl::query()
            ->fromSub($this->makeIndexRequest($request), 'sub')
            ->sum('auto_sum');

        $financialControls = $this->makeIndexRequest($request)
            ->paginate($request->get('per_page') ?? 25);

        return new FinancialControlCollection($financialControls, $totalAutoSum, $totalManualSum);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $this->authorize('create', FinancialControl::class);

            $data = $request->all();
            // $data['created_by'] = auth()->id();

            $financialControl = FinancialControl::create($data);
            $financialControl->handleSetConfirm();
            return $financialControl;
        });
    }

    public function show($payment_method_id, $payment_date)
    {
        $this->authorize('view', FinancialControl::class);

        $financialControls = FinancialControl::where('payment_method_id', $payment_method_id)
            ->whereDate('payment_date', $payment_date)
            ->with(['paymentMethod', 'contractor', 'employee', 'user'])
            ->get();

        $transactions = CashTransactionable::where('payment_method_id', $payment_method_id)
            ->whereDate('created_at', $payment_date)
            ->with('paymentMethod')
            ->get()
            ->map(function ($transaction) {
                // $transaction->contractor = $transaction->contractor;
                $transaction->contragent = $transaction->transactionable->contragent ?? null;
                $transaction->contractor = $transaction->transactionable->contractor ?? null;
                return $transaction;
            });
        return ['financialControls' => $financialControls, 'transactions' => $transactions];
    }

    public function update(Request $request, FinancialControl $financialControl)
    {
        $this->authorize('update', $financialControl);

        $financialControl->update($request->all());

        return $financialControl;
    }

    public function export(Request $request)
    {
        $this->authorize('view', FinancialControl::class);

        $financialControls = $this->index($request);

        $export = new FinancialControlExport($financialControls);
        return Excel::download($export, 'financial_controls_aggregated.xlsx');
    }

    public function exportAllTransactions(Request $request)
    {
        $this->authorize('view', FinancialControl::class);

        $aggregatedControls = $this->makeIndexRequest($request);

        $allTransactions = collect();

        foreach ($aggregatedControls->get() as $control) {
            $payment_method_id = $control->payment_method_id;
            $payment_date = $control->payment_date;

            $financialControls = FinancialControl::where('payment_method_id', $payment_method_id)
                ->whereDate('payment_date', $payment_date)
                ->with(['paymentMethod', 'paymentMethod.legalEntity', 'contractor', 'employee'])
                ->withCasts([
                    'transactionable_type' => TransactionableTypeName::class,
                ])
                ->get();
            $financialControls->each(function ($item) {
                $item->record_type = 'Ручной';
            });

            $transactions = CashTransactionable::where('payment_method_id', $payment_method_id)
                ->whereDate('created_at', $payment_date)
                ->with('paymentMethod', 'paymentMethod.legalEntity')
                ->withCasts([
                    'transactionable_type' => TransactionableTypeName::class,
                ])
                ->get();
            $transactions->each(function ($item) {
                $item->record_type = 'Автоматический';
            });

            // Объединяем финансовые контроли и транзакции в одну коллекцию
            $allTransactions = $allTransactions->merge($financialControls)->merge($transactions)->sortByDesc('created_at');
        }

        // return $allTransactions;
        $export = new FinancialControllAllTransactionsExport($allTransactions);
        return Excel::download($export, 'all_transactions.xlsx');
    }

    public function import(Request $request)
    {
        // Log::debug('Начало импорта');

        // Логирование всех заголовков запроса
        // Log::debug('Заголовки запроса:', $request->headers->all());

        // Логирование всего тела запроса
        // Log::debug('Тело запроса:', $request->all());

        // Log::debug('Все входящие данные: ' . print_r($request->all(), true));

        $file = $request->file('file');
        Log::debug(print_r($file, true));

        if (!$file) {
            Log::debug('Файл не найден');
            return response()->json(['message' => 'Файл не найден.'], 400);
        }

        // Log::debug('Файл получен: ' . $file->getClientOriginalName());

        if (!is_file($file->getPathname())) {
            Log::debug('Полученный объект не является файлом');
            return response()->json(['message' => 'Полученный объект не является файлом.'], 400);
        }

        if (!is_uploaded_file($file->getPathname())) {
            Log::debug('Файл не был загружен через HTTP POST');
            return response()->json(['message' => 'Файл не был загружен через HTTP POST.'], 400);
        }

        $mimeType = $file->getMimeType();
        // Log::debug('MIME-тип файла: ' . $mimeType);

        if ($mimeType != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return response()->json(['message' => 'Файл имеет неправильный MIME-тип.'], 400);
        }

        // Создаем экземпляр сервиса для сопоставления имён сущностей с ID
        $entityMatcher = new EntityNameToIdMatcher();

        // Создаем экземпляр класса импорта
        $import = new FinancialControlImport($entityMatcher);

        DB::beginTransaction();

        try {
            // Процесс импорта
            Excel::import($import, $file, null, \Maatwebsite\Excel\Excel::XLSX); // Добавляем явное указание типа файла

            if ($import->hasErrors()) {
                DB::rollback();
                // Преобразование списка ошибок в JSON с сохранением русских символов
                $errorsJson = json_encode($import->getErrors(), JSON_UNESCAPED_UNICODE);
                Log::debug("Ошибки при импорте: {$errorsJson}");

                return response()->json([
                    'message' => 'Обнаружены ошибки при импорте транзакций.',
                    'errors' => $import->getErrors()
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            // Log::debug('Импорт успешно завершен');
            DB::commit();
            return response()->json(['message' => 'Транзакции успешно импортированы.'], 200);
        } catch (\Throwable $e) {
            // В случае исключения откатываем транзакцию и возвращаем сообщение об ошибке
            Log::debug('Исключение при импорте: ' . $e->getMessage());
            DB::rollback();
            return response()->json(['message' => 'Ошибка при импорте транзакций.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $financialControl = FinancialControl::findOrFail($id);

            $this->authorize('delete', $financialControl);
            $financialControl->handleSetUnconfirm();
            $financialControl->delete();

            return response()->json(null, 204);
        });
    }
}
