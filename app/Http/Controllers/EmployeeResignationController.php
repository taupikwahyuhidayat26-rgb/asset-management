<?php

namespace App\Http\Controllers;

use App\Models\Employee;

class EmployeeResignationController extends BaseEmployeeTransactionController
{
    protected function transactionType(): string
    {
        return 'resignation';
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'tanggal_resign' => ['required', 'date'],
            'alasan' => ['nullable', 'string'],
        ];
    }

    protected function prepareTransaction(array $validated, Employee $employee): array
    {
        return $validated;
    }
}
