@extends('layouts.app')

@section('title', 'Pengelolaan User - VeloAssets')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Pengelolaan User</h3>
            <p class="text-muted small mt-1 mb-0">Kelola akun, password, dan role pengguna aplikasi.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="fa-solid fa-user-plus me-1"></i>Tambah User
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Periksa kembali data yang dimasukkan:</div>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card card-premium bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->username ?: '-' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php($role = $user->isAdmin() ? 'Admin' : ($user->role ?: 'User'))
                                <span class="badge {{ $role === 'Admin' ? 'bg-primary' : ($role === 'Staff' ? 'bg-success' : 'bg-secondary') }}">
                                    {{ $role }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                </button>
                                @if(! $user->is(auth()->user()) && ! (config('access.initial_admin_email') && strcasecmp($user->email, config('access.initial_admin_email')) === 0))
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('users.update', $user) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="form_context" value="edit">
                                        <input type="hidden" name="form_user_id" value="{{ $user->id }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Akun User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="edit_name_{{ $user->id }}" class="form-label">Nama</label>
                                                <input id="edit_name_{{ $user->id }}" name="name" value="{{ old('form_user_id') == $user->id ? old('name', $user->name) : $user->name }}" class="form-control" required maxlength="255">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_username_{{ $user->id }}" class="form-label">Username</label>
                                                <input id="edit_username_{{ $user->id }}" name="username" value="{{ old('form_user_id') == $user->id ? old('username', $user->username) : $user->username }}" class="form-control" required maxlength="255">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_email_{{ $user->id }}" class="form-label">Email</label>
                                                <input id="edit_email_{{ $user->id }}" type="email" name="email" value="{{ old('form_user_id') == $user->id ? old('email', $user->email) : $user->email }}" class="form-control" required maxlength="255">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_role_{{ $user->id }}" class="form-label">Role</label>
                                                <select id="edit_role_{{ $user->id }}" name="role" class="form-select" required @disabled($user->isAdmin())>
                                                    @foreach(['Admin', 'Staff', 'User'] as $option)
                                                        <option value="{{ $option }}" @selected((old('form_user_id') == $user->id ? old('role', $role) : $role) === $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                                @if($user->isAdmin())
                                                    <input type="hidden" name="role" value="Admin">
                                                    <small class="text-muted">Role Admin utama tidak dapat diturunkan.</small>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_password_{{ $user->id }}" class="form-label">Password baru</label>
                                                <input id="edit_password_{{ $user->id }}" type="password" name="password" class="form-control" minlength="8" autocomplete="new-password">
                                                <small class="text-muted">Kosongkan jika password tidak ingin diubah.</small>
                                            </div>
                                            <div>
                                                <label for="edit_password_confirmation_{{ $user->id }}" class="form-label">Konfirmasi password baru</label>
                                                <input id="edit_password_confirmation_{{ $user->id }}" type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada akun user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $users->links() }}</div>
    </div>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_context" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_name" class="form-label">Nama</label>
                            <input id="new_name" name="name" value="{{ old('name') }}" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="new_username" class="form-label">Username</label>
                            <input id="new_username" name="username" value="{{ old('username') }}" class="form-control" required maxlength="255" autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label for="new_email" class="form-label">Email</label>
                            <input id="new_email" type="email" name="email" value="{{ old('email') }}" class="form-control" required maxlength="255" autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label for="new_role" class="form-label">Role</label>
                            <select id="new_role" name="role" class="form-select" required>
                                @foreach(['Admin', 'Staff', 'User'] as $option)
                                    <option value="{{ $option }}" @selected(old('role', 'User') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password</label>
                            <input id="new_password" type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input id="new_password_confirmation" type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i>Buat Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($errors->any() && old('form_context') === 'create')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createUserModal')).show();
            });
        </script>
    @elseif($errors->any() && old('form_context') === 'edit' && old('form_user_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById(@json('editUserModal'.old('form_user_id')))).show();
            });
        </script>
    @endif
@endsection
