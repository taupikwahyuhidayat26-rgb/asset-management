<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Validation\Rule;

class EmployeeDroppointMutationController extends BaseEmployeeTransactionController
{
    protected function transactionType(): string
    {
        return 'droppoint-mutation';
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'tanggal_mutasi_dp' => ['required', 'date'],
            'droppoint_baru_id' => ['required', Rule::exists('droppoint', 'id')->where('status_dp', 'Aktif')],
        ];
    }

    protected function prepareTransaction(array $validated, Employee $employee): array
    {
        return $validated + ['droppoint_lama_id' => $employee->droppoint_id];
    }
}
