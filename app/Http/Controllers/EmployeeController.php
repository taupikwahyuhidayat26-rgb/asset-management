<?php

namespace App\Http\Controllers;

use App\Models\Droppoint;
use App\Models\Employee;
use App\Models\IdCreationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    private const FIELDS = [
        'no_karyawan' => ['No Karyawan', 'text', true],
        'nama_karyawan' => ['Nama Karyawan', 'text', true],
        'posisi_1' => ['Posisi 1', 'text', false],
        'posisi_2' => ['Posisi 2', 'text', false],
        'status_karyawan' => ['Status Karyawan', 'text', false],
        'tanggal_masuk' => ['Tanggal Masuk', 'date', false],
        'masa_kerja' => ['Masa Kerja', 'text', false],
        'no_hp' => ['No. HP', 'text', false],
        'email' => ['Email', 'email', false],
        'nik_ktp' => ['NIK KTP', 'text', false],
        'jenis_kelamin' => ['Jenis Kelamin', 'text', false],
        'bca' => ['BCA', 'text', false],
        'nomor_rekening' => ['Nomor Rekening', 'text', false],
        'nama_rekening' => ['Nama Rekening', 'text', false],
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'kategori' => ['nullable', Rule::in(['Aktif', 'Resign'])],
            'droppoint_id' => 'nullable|integer|exists:droppoint,id',
        ]);

        $query = Employee::with(['droppoint', 'system']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($employeeQuery) use ($search) {
                $employeeQuery->where('no_karyawan', 'like', "%{$search}%")
                    ->orWhere('nama_karyawan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['kategori'])) {
            $query->where('status_kerja', $filters['kategori']);
        }

        if (! empty($filters['droppoint_id'])) {
            $query->where('droppoint_id', $filters['droppoint_id']);
        }

        $employees = $query->orderBy('nama_karyawan')->paginate(10)->withQueryString();
        $droppoints = Droppoint::orderBy('kode_dp')->get();
        $fields = self::FIELDS;

        return view('employees.index', compact('employees', 'droppoints', 'fields', 'filters'));
    }

    public function create()
    {
        $droppoints = Droppoint::orderBy('kode_dp')->get();
        $fields = self::FIELDS;

        return view('employees.create', compact('droppoints', 'fields'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated) {
            $employeeData = $validated['employee'];
            $systemData = $validated['system'];
            $employee = Employee::create($employeeData);

            if ($systemData['id_sebelumnya'] || $systemData['nama_id_sebelumnya']) {
                $employee->system()->create($systemData);
            }

            IdCreationRequest::create([
                'employee_id' => $employee->id,
                'status_pemrosesan' => 'Menunggu',
            ]);
        });

        return back()->with('success', 'Data karyawan dan permintaan pembuatan ID berhasil ditambahkan.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validatedData($request, $employee);

        DB::transaction(function () use ($validated, $employee) {
            $employee->update($validated['employee']);

            if ($validated['system']['id_sebelumnya'] || $validated['system']['nama_id_sebelumnya']) {
                $employee->system()->updateOrCreate([], $validated['system']);
            } else {
                $employee->system()->delete();
            }
        });

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with('success', 'Data karyawan berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Employee $employee = null): array
    {
        $rules = [];
        foreach (self::FIELDS as $name => [, $type, $required]) {
            $rules[$name] = ($required ? 'required' : 'nullable').($type === 'email' ? '|email|max:255' : '|max:255');
        }

        $rules['no_karyawan'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('employees', 'no_karyawan')->ignore($employee?->id),
        ];
        $rules['tanggal_masuk'] = 'nullable|date';
        $rules['status_kerja'] = ['required', Rule::in(['Aktif', 'Resign'])];
        $rules['droppoint_id'] = 'nullable|exists:droppoint,id';
        $rules['id_sebelumnya'] = 'nullable|string|max:255';
        $rules['nama_id_sebelumnya'] = 'nullable|string|max:255';

        $data = $request->validate($rules);
        $employeeData = array_intersect_key($data, array_flip(array_keys(self::FIELDS)));
        $employeeData['status_kerja'] = $data['status_kerja'];
        $employeeData['droppoint_id'] = $data['droppoint_id'] ?? null;

        return [
            'employee' => $employeeData,
            'system' => [
                'id_sebelumnya' => $data['id_sebelumnya'] ?? null,
                'nama_id_sebelumnya' => $data['nama_id_sebelumnya'] ?? null,
            ],
        ];
    }
}
