<?php

namespace App\Http\Controllers;

use App\Models\Employee;

class EmployeeTransactionController extends BaseEmployeeTransactionController
{
    protected function transactionType(): string
    {
        return 'id-creation';
    }

    protected function rules(): array
    {
        return [];
    }

    protected function prepareTransaction(array $validated, Employee $employee): array
    {
        return $validated;
    }
}
