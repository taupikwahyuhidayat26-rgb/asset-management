<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('name')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['Admin', 'Staff', 'User'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return to_route('users.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['Admin', 'Staff', 'User'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($this->isConfiguredAdmin($user) && $validated['role'] !== 'Admin') {
            throw ValidationException::withMessages([
                'role' => 'Role Admin utama tidak dapat diturunkan.',
            ]);
        }

        if ($user->is(auth()->user()) && $validated['role'] !== 'Admin') {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat menurunkan role akun yang sedang digunakan.',
            ]);
        }

        if ($validated['role'] !== 'Admin' && $user->role === 'Admin') {
            if (! $this->hasOtherAdmin($user)) {
                throw ValidationException::withMessages([
                    'role' => 'Minimal harus ada satu akun Admin.',
                ]);
            }
        }

        $password = $validated['password'] ?? null;
        unset($validated['password']);

        if ($password !== null) {
            $validated['password'] = Hash::make($password);
        }

        $user->update($validated);

        return to_route('users.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
        }

        if ($this->isConfiguredAdmin($user)) {
            return back()->withErrors(['user' => 'Akun Admin utama tidak dapat dihapus.']);
        }

        if ($user->role === 'Admin' && ! $this->hasOtherAdmin($user)) {
            return back()->withErrors(['user' => 'Minimal harus ada satu akun Admin.']);
        }

        $user->delete();

        return to_route('users.index')->with('success', 'Akun berhasil dihapus.');
    }

    private function hasOtherAdmin(User $user): bool
    {
        return User::query()
            ->whereKeyNot($user->id)
            ->where(function ($query) {
                $query->where('role', 'Admin');

                if (config('access.initial_admin_email')) {
                    $query->orWhereRaw('LOWER(email) = ?', [strtolower(config('access.initial_admin_email'))]);
                }
            })
            ->exists();
    }

    private function isConfiguredAdmin(User $user): bool
    {
        $adminEmail = config('access.initial_admin_email');

        return $adminEmail !== null
            && $user->email !== null
            && strcasecmp($user->email, $adminEmail) === 0;
    }
}
