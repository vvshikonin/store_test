<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Defect;
use Illuminate\Http\Request;
use App\Http\Resources\V1\Defect\DefectResource;
use App\Http\Resources\V1\Defect\DefectFullResource;
use App\Models\V1\OrderProduct;
use Illuminate\Database\Query\JoinClause;
use App\Exports\DefectProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\V1\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\Entities\MoneyRefundService;
use App\Services\TransactionService;
use App\Services\Entities\Defects\DefectService;
class DefectController extends Controller
{

    private $moneyRefundService;
    private $transactionService;
    protected $defectService;

    public function __construct(MoneyRefundService $moneyRefundService, TransactionService $transactionService, DefectService $defectService)
    {
        $this->moneyRefundService = $moneyRefundService;
        $this->transactionService = $transactionService;
        $this->defectService = $defectService;
    }

    public function index(Request $request)
    {
        $orderProductsSubQuery = OrderProduct::select('order_id')
            ->selectRaw('GROUP_CONCAT(DISTINCT contractors.name) AS contractor_name')
            ->selectRaw('SUM(amount) as amount')
            ->selectRaw('SUM(amount * avg_price) as sum')
            ->leftJoin('contractors', 'contractors.id', '=', 'order_products.contractor_id')
            ->groupBy('order_id');

        $defects = Defect::select('defects.*', 'orders.number as number', 'order_products.sum')
            ->leftJoinSub($orderProductsSubQuery, 'order_products', function ($join) {
                $join->on('order_products.order_id', '=', 'defects.order_id');
            })
            ->leftJoin('legal_entities', 'legal_entities.id', '=', 'defects.legal_entity_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'defects.payment_method_id')
            ->leftJoin('orders', 'orders.id', '=', 'defects.order_id')
            ->applyFilters($request);

        $perPage = $request->get('per_page');
        $sortField = $request->get('sort_field');
        $sortType = $request->get('sort_type');

        if ($sortField !== null && $sortType !== null) {
            $defects = $defects->orderBy($sortField, $sortType);
        }

        if ($perPage !== null) {
            $defects = $defects->paginate($perPage);
        } else {
            $defects = $defects->paginate(15);
        }

        return DefectResource::collection($defects);
    }

    public function store(Request $request)
    {
        $defect = Defect::create($request->all());
        return response()->json($defect, 201);
    }

    public function show($id)
    {
        $defect = Defect::with(['creator', 'updater'])->findOrFail($id);
        return new DefectFullResource($defect);
    }

    public function update(Request $request, Defect $defect)
    {
        $updatedDefect = $this->defectService->update($defect, $request->all());
        return $updatedDefect;
    }

    public function exportDefectProducts(Request $request)
    {
        $defectSubQuery = Defect::select('order_id');
        $defectSubQuery = $defectSubQuery->applyFilters($request);


        $orderProducts = OrderProduct::select('products.main_sku', 'products.name', 'defects.*', 'orders.number', 'order_products.avg_price', 'legal_entities.name as legal_entity_name', 'payment_methods.name as payment_method_name', 'order_statuses.name as order_status')
            ->addSelect(DB::raw('GROUP_CONCAT(DISTINCT contractors.name SEPARATOR ", ") as contractor_names'))
            ->whereIn('order_products.order_id', $defectSubQuery)
            ->leftJoin('products', 'products.id', '=', 'order_products.product_id')
            ->leftJoin('defects', 'defects.order_id', '=', 'order_products.order_id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.order_status_id')
            ->leftJoin('legal_entities', 'legal_entities.id', '=', 'defects.legal_entity_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'defects.payment_method_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'order_products.contractor_id')
            ->groupBy('order_products.order_id', 'products.main_sku', 'products.name', 'defects.id', 'orders.number', 'order_products.avg_price', 'legal_entities.name', 'payment_methods.name', 'order_statuses.name')
            ->get();

        return Excel::download(new DefectProductsExport($orderProducts), 'defectProducts.xlsx');
    }

    /**
     * Обрабатывает загрузку на сервер файлов браков
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\V1\Defect $defect
     * @return \Illuminate\Http\Response
     */
    public function loadFiles(Request $request, Defect $defect)
    {
        $newFiles = $defect->saveDefectFiles($request->file('files'));
        $currentFiles = json_decode($defect->files, true) ?? [];

        foreach ($newFiles as $hash => $path) {
            if (!isset($currentFiles[$hash])) {
                $currentFiles[$hash] = $path;
            }
        }

        $defect->files = json_encode($currentFiles);
        $defect->save();

        return new DefectFullResource($defect);
    }

    public function deleteFile(Request $request, Defect $defect)
    {
        $hash = $request->input('link');
        $files = json_decode($defect->files, true) ?? [];

        if (isset($files[$hash])) {
            Storage::disk('public')->delete($files[$hash]);

            unset($files[$hash]);

            if (empty($files)) {
                $defect->files = null;
            } else {
                $defect->files = json_encode($files);
            }

            $defect->save();
        }

        return new DefectFullResource($defect);
    }

    public function destroy(Defect $defect)
    {
        $defect->delete();
        return response()->json(null, 204);
    }
}
