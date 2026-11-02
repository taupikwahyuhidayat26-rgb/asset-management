<?php

namespace App\Http\Controllers;

use App\Models\Droppoint;
use App\Models\Employee;
use App\Models\EmployeeDroppointMutation;
use App\Models\EmployeePositionMutation;
use App\Models\EmployeeResignation;
use App\Models\IdCreationRequest;
use App\Models\SystemIdChange;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

abstract class BaseEmployeeTransactionController extends Controller
{
    private const DEFINITIONS = [
        'id-creation' => [
            'title' => 'Pembuatan ID',
            'model' => IdCreationRequest::class,
            'fields' => [],
            'columns' => ['employee.no_karyawan' => 'No Karyawan', 'employee.nama_karyawan' => 'Nama Karyawan'],
        ],
        'system-id' => [
            'title' => 'Perubahan ID Sistem',
            'model' => SystemIdChange::class,
            'fields' => [
                'employee_id' => ['Karyawan', 'employee'],
                'id_aktif' => ['ID Aktif Baru', 'text'],
                'tanggal_update' => ['Tanggal Update', 'date'],
            ],
            'columns' => ['employee.no_karyawan' => 'No Karyawan', 'employee.nama_karyawan' => 'Nama', 'id_sebelumnya' => 'ID Sebelumnya', 'id_aktif' => 'ID Aktif', 'tanggal_update' => 'Tanggal Update'],
        ],
        'resignation' => [
            'title' => 'Pengunduran Diri',
            'model' => EmployeeResignation::class,
            'fields' => [
                'employee_id' => ['Karyawan', 'employee'],
                'tanggal_resign' => ['Tanggal Resign', 'date'],
                'alasan' => ['Alasan', 'textarea'],
            ],
            'columns' => ['employee.no_karyawan' => 'No Karyawan', 'employee.nama_karyawan' => 'Nama', 'tanggal_resign' => 'Tanggal Resign', 'alasan' => 'Alasan'],
        ],
        'position-mutation' => [
            'title' => 'Mutasi Posisi',
            'model' => EmployeePositionMutation::class,
            'fields' => [
                'employee_id' => ['Karyawan', 'employee'],
                'posisi_baru' => ['Posisi Baru', 'text'],
                'status_karyawan_baru' => ['Status Karyawan Baru', 'text'],
                'tanggal_mutasi' => ['Tanggal Mutasi', 'date'],
                'keterangan' => ['Keterangan', 'textarea'],
            ],
            'columns' => ['employee.no_karyawan' => 'No Karyawan', 'employee.nama_karyawan' => 'Nama', 'posisi_1_lama' => 'Posisi Lama', 'posisi_baru' => 'Posisi Baru', 'tanggal_mutasi' => 'Tanggal Mutasi'],
        ],
        'droppoint-mutation' => [
            'title' => 'Mutasi Drop Point',
            'model' => EmployeeDroppointMutation::class,
            'fields' => [
                'employee_id' => ['Karyawan', 'employee'],
                'tanggal_mutasi_dp' => ['Tanggal Mutasi DP', 'date'],
                'droppoint_baru_id' => ['Drop Point Baru', 'droppoint'],
            ],
            'columns' => ['employee.no_karyawan' => 'No Karyawan', 'employee.nama_karyawan' => 'Nama', 'oldDroppoint.kode_dp' => 'Kode DP Lama', 'newDroppoint.kode_dp' => 'Kode DP Baru', 'tanggal_mutasi_dp' => 'Tanggal Mutasi'],
        ],
    ];

    abstract protected function transactionType(): string;

    abstract protected function rules(): array;

    abstract protected function prepareTransaction(array $validated, Employee $employee): array;

    public function index()
    {
        $type = $this->transactionType();
        $definition = $this->definition($type);
        $model = $definition['model'];
        $query = $model::query()->with(['employee']);
        if ($type === 'droppoint-mutation') {
            $query->with(['oldDroppoint', 'newDroppoint']);
        }

        $transactions = $query->latest()->paginate(10);
        $statuses = ['Menunggu', 'Disetujui', 'Ditolak'];

        $view = $this->viewName($type);

        return view($view, compact('definition', 'transactions', 'statuses', 'type'));
    }

    public function create()
    {
        $type = $this->transactionType();
        abort_if($type === 'id-creation', 404);
        $definition = $this->definition($type);
        $employees = Employee::orderBy('nama_karyawan')->get();
        $droppoints = Droppoint::query()
            ->when($type === 'droppoint-mutation', fn ($query) => $query->where('status_dp', 'Aktif'))
            ->orderBy('kode_dp')
            ->get();

        return view($this->viewName($type).'.create', compact('definition', 'employees', 'droppoints', 'type'));
    }

    public function store(Request $request)
    {
        $type = $this->transactionType();
        abort_if($type === 'id-creation', 404);
        $definition = $this->definition($type);
        $validated = $request->validate($this->rules());
        $employee = Employee::findOrFail($validated['employee_id']);
        $data = $this->prepareTransaction($validated, $employee);
        $data['status_pemrosesan'] = 'Menunggu';

        $definition['model']::create($data);

        return redirect()
            ->route($definition['routes']['list'])
            ->with('success', 'Transaksi berhasil dibuat dengan status Menunggu.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $type = $this->transactionType();
        $validated = $request->validate([
            'status_pemrosesan' => ['required', Rule::in(['Menunggu', 'Disetujui', 'Ditolak'])],
        ]);
        $definition = $this->definition($type);
        $model = $definition['model'];
        $transaction = $model::findOrFail($id);
        $transaction->update($validated);

        return back()->with('success', 'Status pemrosesan berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $type = $this->transactionType();
        $definition = $this->definition($type);
        $model = $definition['model'];
        $transaction = $model::findOrFail($id);
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    protected function definition(string $type): array
    {
        abort_unless(isset(self::DEFINITIONS[$type]), 404);

        $definition = self::DEFINITIONS[$type];
        $suffix = $this->routeSuffix($type);
        $definition['routes'] = [
            'list' => "employee_transactions.{$suffix}",
            'create' => "employee_transactions.{$suffix}.create",
            'store' => "employee_transactions.{$suffix}.store",
            'status' => "employee_transactions.{$suffix}.status",
            'destroy' => "employee_transactions.{$suffix}.destroy",
        ];

        return $definition;
    }

    private function viewName(string $type): string
    {
        return match ($type) {
            'system-id' => 'employees.transactions.system_id.system_id',
            'resignation' => 'employees.transactions.resignation.resignation',
            'position-mutation' => 'employees.transactions.position_mutation.position_mutation',
            'droppoint-mutation' => 'employees.transactions.droppoint_mutation.droppoint_mutation',
            default => 'employees.transactions',
        };
    }

    private function routeSuffix(string $type): string
    {
        return match ($type) {
            'id-creation' => 'id_creation',
            'system-id' => 'system_id',
            'resignation' => 'resignation',
            'position-mutation' => 'position_mutation',
            'droppoint-mutation' => 'droppoint_mutation',
            default => abort(404),
        };
    }
}
