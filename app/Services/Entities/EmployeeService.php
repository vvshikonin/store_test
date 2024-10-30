<?php

namespace App\Services\Entities;
use App\Models\V1\Employee;

class EmployeeService {
    public function update(Employee $employee, $data)
    {
        return $employee->update($data);
    }

    public function create($data)
    {
        return Employee::create($data);
    }

    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();
        } catch(\Exception $e) {
            return response()->json(['message' => 'Невозможно удалить ответственного, пока он связан хотя бы с одной из транзакций']);
        }
        return true;
    }
}