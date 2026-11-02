<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SystemIdChange;

class SystemIdChangeController extends BaseEmployeeTransactionController
{
    protected function transactionType(): string
    {
        return 'system-id';
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'id_aktif' => ['required', 'string', 'max:255'],
            'tanggal_update' => ['required', 'date'],
        ];
    }

    protected function prepareTransaction(array $validated, Employee $employee): array
    {
        $previousId = SystemIdChange::where('employee_id', $employee->id)->latest()->value('id_aktif')
            ?? $employee->system?->id_sebelumnya;

        return $validated + ['id_sebelumnya' => $previousId];
    }
}
