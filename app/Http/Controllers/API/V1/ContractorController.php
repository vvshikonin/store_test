<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ContractorResource;
use App\Models\V1\Contractor;
use Illuminate\Http\Request;
use App\Exports\ContractorsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContractorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (empty($request->all())) {
            return ContractorResource::collection(
                Contractor::orderBy(
                    'name',
                    'asc'
                )->get()
            );
        }

        $contractors = Contractor::query();

        $contractors = $this->applyFilters($contractors, $request);

        $contractors = $contractors->orderBy(
            $request->input('sortField') ?? 'name',
            $request->input('sortType') ?? 'desc'
        )->paginate($request->perPage ?? 25);

        return ContractorResource::collection($contractors);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'contractor_create')->count()) {
            return response('', 403);
        }

        $new_contractor = new Contractor();
        $new_contractor->fill($request->all());
        $new_contractor->symbolic_code_list = json_encode($request->get('symbolic_code_list'));
        $new_contractor->save();

        return new ContractorResource($new_contractor);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Contractor $contractor)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'contractor_read')->count()) {
            return response('', 403);
        }

        return new ContractorResource($contractor);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contractor $contractor)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'contractor_update')->count()) {
            return response('', 403);
        }

        $contractor->fill($request->all());
        $contractor->symbolic_code_list = json_encode($request->get('symbolic_code_list'));

        $contractor->save();

        return $contractor;
    }

    /**
     * Отправляет запрос на импорт таблицы поставщиков
     * 
     * @param Request $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function export(Request $request)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'contractor_read')->count()) {
            return response('', 403);
        }

        $sort_type = $request->get('sortType');
        $sort_field = $request->get('sortField');

        $contractors = Contractor::select('*');

        $contractors = $this->applyFilters($contractors, $request);

        if ($sort_type && $sort_field) {
            $contractors->orderBy($sort_field, $sort_type);
        }

        $contractors = $contractors->get();

        return Excel::download(new ContractorsExport($contractors), 'contractors.xlsx');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contractor $contractor)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'contractor_delete')->count()) {
            return response('', 403);
        }

        try {
            $contractor->delete();
        } catch (\Exception $e) {
            throw new HttpException(422, "Невозможно удалить этого поставщика!");
        }

        return 'success';
    }

    /**
     * Применяет фильтрацию к запросу выборки экземпляров модели.
     * 
     * @param Query $contractors
     * @param Request $request
     * @return $contractors
     */
    private function applyFilters($contractors, $request)
    {
        $filters = json_decode($request->filters);

        if (isset($filters->name) && !is_null($filters->name))
            $contractors = $contractors->nameFilter($filters->name);

        if (isset($filters->marginality) && !is_null($filters->marginality))
            $contractors = $contractors->marginalityFilter($filters->marginality);

        if (isset($filters->legalEntity) && !is_null($filters->legalEntity))
            $contractors = $contractors->legalEntityFilter($filters->legalEntity);

        if (isset($filters->minAmount) && !is_null($filters->minAmount))
            $contractors = $contractors->minOrderAmountFilter($filters->minAmount);

        if (isset($filters->pickupTime) && !is_null($filters->pickupTime))
            $contractors = $contractors->pickupTimeFilter($filters->pickupTime);

        if (isset($filters->warehouse) && !is_null($filters->warehouse))
            $contractors = $contractors->warehouseFilter($filters->warehouse);

        if (isset($filters->paymentDelay) && !is_null($filters->paymentDelay))
            $contractors = $contractors->paymentDelayFilter($filters->paymentDelay);

        if (isset($filters->deliveryContract) && !is_null($filters->deliveryContract))
            $contractors = $contractors->deliveryContractFilter($filters->deliveryContract);

        if (isset($filters->mainContractor) && !is_null($filters->mainContractor))
            $contractors = $contractors->mainContractorFilter($filters->mainContractor);

        if (isset($filters->workingCondition) && !is_null($filters->workingCondition))
            $contractors = $contractors->workingConditionsFilter($filters->workingCondition);

        return $contractors;
    }

    /**
     * Проверяет наличие символьного кода у поставщика
     * 
     * @param string $code
     * @return bool
     */
    public function checkSymbolicCode(Request $request)
    {
        $contractor = Contractor::whereJSONContains('symbolic_code_list', $request->get('code'))->first();

        if ($contractor)
            return true;

        return false;
    }
}
