<?php

namespace App\Http\Controllers;

use App\Models\Employee;

class EmployeePositionMutationController extends BaseEmployeeTransactionController
{
    protected function transactionType(): string
    {
        return 'position-mutation';
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'posisi_baru' => ['required', 'string', 'max:255'],
            'status_karyawan_baru' => ['nullable', 'string', 'max:255'],
            'tanggal_mutasi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    protected function prepareTransaction(array $validated, Employee $employee): array
    {
        return $validated + [
            'posisi_1_lama' => $employee->posisi_1,
            'posisi_2_lama' => $employee->posisi_2,
            'status_karyawan_lama' => $employee->status_karyawan,
            'droppoint_lama_id' => $employee->droppoint_id,
        ];
    }
}
