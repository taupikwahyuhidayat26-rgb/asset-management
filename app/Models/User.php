<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // CATATAN: Jika nama tabelmu di database tertulis tunggal yaitu 'user',
    // aktifkan baris di bawah ini. Tapi jika jamak 'users' (bawaan laravel), hapus/komentari baris ini.
    protected $table = 'users';

    protected $fillable = [
        'name',
        'username', // KUNCI UTAMA: Wajib daftarkan username di sini
        'email',    // Biarkan saja jika kolom emailnya masih ada di database
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'Admin'
            || ($this->email !== null
                && config('access.initial_admin_email')
                && strcasecmp($this->email, config('access.initial_admin_email')) === 0);
    }

    public function canManageData(): bool
    {
        return $this->isAdmin() || $this->role === 'Staff';
    }

    public function canViewData(): bool
    {
        return in_array($this->role, ['Admin', 'Staff', 'User'], true) || $this->isAdmin();
    }
}
