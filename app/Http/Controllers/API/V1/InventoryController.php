<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InventoryResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryExport;
use App\Exports\CompletedInventoryExport;
use App\Models\V1\Product;
use App\Models\V1\Inventory;
use App\Models\V1\InventoryProduct;
use Illuminate\Http\Request;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InventoryController extends Controller
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Возвращает список инвентаризаций с учётом фильтров, сортировки и пагинации.
     *
     * @param Illuminate\Http\Request $request
     * @return App\Http\Resources\V1\InventoryResource
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Inventory::class);

        $sort_type = $request->get('sort_type');
        $sort_field = $request->get('sort_field');
        $per_page = $request->get('per_page');

        $product_filter = $request->get('product');
        $status_filter = $request->get('status');
        $user_filter = $request->get('user');
        $date_start_filter = $request->get('date_start_filter');
        $date_end_filter = $request->get('date_end_filter');
        $date_equal_filter = $request->get('date_equal_filter');
        $date_notEqual_filter = $request->get('date_notEqual_filter');

        $inventories = Inventory::select('*');

        if ($product_filter != null) {
            $inventories = $inventories->inventoryProductFilter($product_filter);
        }
        if ($status_filter != null) {
            $inventories = $inventories->statusFilter($status_filter);
        }
        if ($user_filter != null) {
            $inventories = $inventories->userFilter($user_filter);
        }
        if ($date_start_filter || $date_end_filter || $date_equal_filter || $date_notEqual_filter) {
            $inventories = $inventories->inventoryDateFilter($date_start_filter, $date_end_filter, $date_equal_filter, $date_notEqual_filter);
        }

        if ($sort_type && $sort_field) {
            $inventories = $inventories->orderBy($sort_field, $sort_type);
        }

        if ($per_page) {
            $inventories = $inventories->paginate($per_page);
        } else {
            $inventories = $inventories->paginate(15);
        }
        return InventoryResource::collection($inventories);
    }

    /**
     * Возвращает выбранную инвентаризацию с подключением связанных с ней продуктов инвентаризации.
     *
     * @param App\Models\V1\Inventory $inventory
     * @return array
     */
    public function show(Inventory $inventory)
    {
        $this->authorize('view', $inventory);

        return  [
            'inventory' => new InventoryResource($inventory),
            'inventoryProducts' => $inventory->inventoryProducts,
        ];
    }

    /**
     * Обрабатывает создание новой инвентаризации с заполнением продуктов инвентаризации из числа товаров
     * с ненулевым остатком на складе и выбором максимальной закупочной цены
     *
     * @return App\Models\V1\Inventory $inventory
     */
    public function store()
    {
        $this->authorize('create', Inventory::class);

        $inventory = new Inventory;
        $inventory->user_id = auth()->user()->id;
        $inventory->created_by = auth()->user()->id;
        $inventory->is_completed = false;
        $inventory->save();

        $products = Product::select('products.id AS product_id')
            ->selectRaw(' @inventory_id := ' . $inventory->id . ' AS inventory_id')
            ->selectRaw('SUM(stocks.amount) AS original_stock')
            ->selectRaw('MAX(price) AS max_price')
            ->selectRaw('brands.id AS brand_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->havingRaw('original_stock > ?', [0])
            ->groupBy('products.id')
            ->get()
            ->toArray();

        InventoryProduct::insert($products);

        return $inventory;
    }

    /**
     * Обрабатывает обновление данных инвентаризации и ее завершение
     *
     * @param Illuminate\Http\Request $request
     * @param App\Models\V1\Inventory $inventory
     * @return array
     */
    public function update(Request $request, Inventory $inventory)
    {
        $this->authorize('update', $inventory);

        if ($request->has('deleted_products_ids') && !$inventory->is_completed) {
            InventoryProduct::whereIn('id', $request->deleted_products_ids)->delete();
        }

        if ($request->has('inventory_products') && !$inventory->is_completed) {
            InventoryProduct::upsert($request->inventory_products, ['id', 'inventory_id'], ['revision_stock', 'manual_name', 'manual_sku', 'brand_id']);
        }

        if ($request->has('complete_status')) {
            $manualy_added_inventory_products_count = InventoryProduct::where('inventory_id', $inventory->id)
                ->where('is_manually_added', 1)
                ->where(function ($query) {
                    $query->where('manual_sku', null)
                        ->orWhere('manual_name', null)
                        ->orWhere('brand_id', null);
                })
                ->count();

            if ($manualy_added_inventory_products_count) {
                return response()->json(['message' => 'Заполните все данные для новых, добавленных вручную, товаров']);
            }
            $inventory->is_completed = $request->complete_status;
        }

        $inventory->updated_by = auth()->user()->id;
        $inventory->save();

        return  [
            'inventory' => new InventoryResource($inventory),
            'inventoryProducts' => $inventory->inventoryProducts,
        ];
    }

    /**
     * Обрабатывает корректировку продуктов инвентаризации
     *
     * @param Illuminate\Http\Request $request
     * @return App\Models\V1\Inventory $inventory
     */
    public function correct(Request $request)
    {
        $inventory = Inventory::find($request->inventory_id);

        $this->authorize('correct', $inventory);

        $inventoryProduct = InventoryProduct::find($request->id);

        DB::transaction(function () use (&$inventoryProduct, $request) {
            $transactionStock = abs($request->get('difference'));
            if ($request->is_manually_added) {
                // $inventoryProduct->original_stock = $request->revision_stock;
                $inventoryProduct->save();

                // Транзакция поступления
                $this->transactionService->makeIncomingTransaction(
                    $inventoryProduct,
                    $request->revision_stock,
                    $request->product_id,
                    $request->contractor_id,
                    $request->price
                );
            } else {
                // $inventoryProduct->original_stock = $request->revision_stock;
                $inventoryProduct->save();

                if ($request->difference > 0) {
                    $this->transactionService->makeIncomingTransaction(
                        $inventoryProduct,
                        $transactionStock,
                        $request->product_id,
                        $request->contractor_id,
                        $request->price
                    );
                } else {
                    $this->transactionService->makeOutcomingTransaction(
                        $inventoryProduct,
                        $transactionStock,
                        $request->product_id,
                        $request->contractor_id
                    );
                }
            }

            $inventoryProduct->is_corrected = true;
            $inventoryProduct->save();
        });

        $inventory->updated_by = auth()->user()->id;
        $inventory->save();

        return $inventory;
    }

    /**
     * Обрабатывает экспорт с пустым полем всех продуктов инвентаризации
     *
     * @param Illuminate\Http\Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function export(Request $request)
    {
        $sortField = $request->get('sort_field');
        $sortType = $request->get('sort_type');
        $inventoryId = $request->get('id');

        $productFilter = $request->get('product');
        $brandsFilter = $request->get('brands');

        $inventoryProducts = InventoryProduct::select(
            'inventory_products.manual_sku',
            'inventory_products.manual_name',
            'inventory_products.is_manually_added',
            'products.name',
            'products.main_sku AS sku',
            'brands.name AS brand_name'
        )
            ->leftJoin('products', 'products.id', '=', 'inventory_products.product_id')
            ->leftJoin('brands', 'brands.id', '=', DB::raw('COALESCE(inventory_products.brand_id, products.brand_id)'))
            ->where('inventory_id', $inventoryId)
            ->where(function ($query) use ($productFilter) {
                $productsIdsSubQuery = Product::select('id')->productFilter($productFilter);

                $query->where('inventory_products.manual_sku', 'LIKE', '%' . $productFilter . '%')
                    ->orWhere('inventory_products.manual_name', 'LIKE', '%' . $productFilter . '%')
                    ->orWhereIn('product_id', $productsIdsSubQuery);
            })
            ->where(function ($query) use ($brandsFilter) {
                if (!is_null($brandsFilter)) {
                    $query->whereIn('inventory_products.brand_id', $brandsFilter)
                        ->orWhereIn('products.brand_id', $brandsFilter);
                }
            });

        $inventoryProducts = $inventoryProducts->orderBy($sortField, $sortType)->get();

        return Excel::download(new InventoryExport($inventoryProducts), 'inventory.xlsx');
    }

    /**
     * Обрабатывает экспорт всех продуктов инвентаризации после ее завершения с подсчетом остатков
     *
     * @param Illuminate\Http\Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function exportCompleted(Request $request)
    {
        $sortField = $request->get('sort_field');
        $sortType = $request->get('sort_type');
        $inventoryId = $request->get('id');

        $productFilter = $request->get('product');
        $brandsFilter = $request->get('brands');

        $inventoryProducts = InventoryProduct::select(
                'inventory_products.manual_sku',
                'inventory_products.manual_name',
                'inventory_products.is_manually_added',
                'products.name',
                'products.main_sku AS sku',
                'brands.name AS brand_name'
            )
            ->selectRaw('COALESCE(original_stock, 0) AS original_stock')
            ->selectRaw('COALESCE(revision_stock, 0) AS revision_stock')
            ->selectRaw('COALESCE(ABS(original_stock - revision_stock), 0) AS difference')
            ->leftJoin('products', 'products.id', '=', 'inventory_products.product_id')
            ->leftJoin('brands', 'brands.id', '=', DB::raw('COALESCE(inventory_products.brand_id, products.brand_id)'))
            ->where('inventory_id', $inventoryId)
            ->where(function ($query) use ($productFilter) {
                $productsIdsSubQuery = Product::select('id')->productFilter($productFilter);

                $query->where('inventory_products.manual_sku', 'LIKE', '%' . $productFilter . '%')
                    ->orWhere('inventory_products.manual_name', 'LIKE', '%' . $productFilter . '%')
                    ->orWhereIn('product_id', $productsIdsSubQuery);
            })
            ->where(function ($query) use ($brandsFilter) {
                if (!is_null($brandsFilter)) {
                    $query->whereIn('inventory_products.brand_id', $brandsFilter)
                        ->orWhereIn('products.brand_id', $brandsFilter);
                }
            });

        $inventoryProducts = $inventoryProducts->orderBy($sortField, $sortType)->get();

        return Excel::download(new CompletedInventoryExport($inventoryProducts), 'inventory.xlsx');
    }

    /**
     * Обрабатывает удаление инвентаризации
     *
     * @param App\Models\V1\Inventory $inventory
     */
    public function destroy(Inventory $inventory)
    {
        $this->authorize('delete', $inventory);

        if ($inventory->is_completed == true)
            throw new HttpException(422, "Нельзя удалить завершённую инвентаризацию!");

        InventoryProduct::where('inventory_id', $inventory->id)->delete();
        $inventory->delete();
    }
}
