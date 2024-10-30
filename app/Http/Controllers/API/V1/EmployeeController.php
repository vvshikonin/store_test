<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Employee;
use Illuminate\Http\Request;
use App\Services\Entities\EmployeeService;
use App\Models\V1\FinancialControl;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::select('*')
            ->isPaymentResponsible();

        return $employees->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, EmployeeService $employeeSerivce)
    {
        $this->authorize('create', FinancialControl::class);
        return $employeeSerivce->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\V1\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\V1\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee, EmployeeService $employeeSerivce)
    {
        $this->authorize('update', FinancialControl::first());
        return $employeeSerivce->update($employee, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\V1\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee, EmployeeService $employeeSerivce)
    {
        $this->authorize('delete', FinancialControl::first());
        return $employeeSerivce->destroy($employee);
    }
}
