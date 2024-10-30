<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Brand;
use Illuminate\Http\Request;
use App\Http\Resources\V1\BrandResource;
use App\Exports\BrandsExport;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Maatwebsite\Excel\Facades\Excel;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $name_filter = $request->get('nameFilter');
        $sort_type = $request->get('sortType');
        $sort_field = $request->get('sortField');
        $per_page = $request->get('perPage');

        $brands = Brand::select('*');

        if ($name_filter) {
            $brands = $brands->nameFilter($name_filter);
        }

        if ($sort_type && $sort_field) {
            $brands = $brands->orderBy($sort_field, $sort_type);
        }

        if ($per_page) {
            $brands = $brands->paginate($per_page);
        } else {
            $brands = $brands->paginate(1000);
        }

        return BrandResource::collection($brands);
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
        if (!$permissions->where('name', 'brand_create')->count()) {
            return response('', 403);
        }

        $new_brand = new Brand;
        $new_brand->fill($request->all());
        $new_brand->save();
        return $new_brand;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\V1\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'brand_update')->count()) {
            return response('', 403);
        }

        $brand->fill($request->all());
        $brand->save();
        
        return $brand;
    }

    /**
     * Экспортирует все бренды с учетом сортировок и фильтров
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function export(Request $request)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'brand_read')->count()) {
            return response('', 403);
        }

        $name_filter = $request->get('nameFilter');
        $sort_type = $request->get('sortType');
        $sort_field = $request->get('sortField');

        $brands = Brand::select('*');

        if ($name_filter) {
            $brands->nameFilter($name_filter);
        }

        if ($sort_type && $sort_field) {
            $brands->orderBy($sort_field, $sort_type);
        }

        $brands = $brands->get();

        return Excel::download(new BrandsExport($brands), 'brands.xlsx');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\V1\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'brand_delete')->count()) {
            return response('', 403);
        }

        try {
            $brand->delete();
        } catch (\Exception $e) {
            throw new HttpException(422, "Невозможно удалить бренд связанный с товарами!");
        }
    }
}
