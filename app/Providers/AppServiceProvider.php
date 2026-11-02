<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\Menu; // Pastikan kamu sudah punya model Menu

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 2. KUNCI UTAMA: Paksa paginasi menggunakan gaya Bootstrap 5
        Paginator::useBootstrapFive();

        // Kodingan menu dinamis (menuGroups) biarkan saja tetap di sini...
        View::composer('*', function ($view) {
            $menuGroups = \App\Models\MenuGroup::with('menus')->orderBy('id', 'asc')->get();
            $view->with('menuGroups', $menuGroups);
        });
    }
}