<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $masterId = DB::table('menu_groups')->where('name', 'Master Data')->value('id');
        if (! $masterId) {
            $masterId = DB::table('menu_groups')->insertGetId([
                'name' => 'Master Data',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $transactionId = DB::table('menu_groups')->where('name', 'Transaksi')->value('id');
        if (! $transactionId) {
            $transactionId = DB::table('menu_groups')->insertGetId([
                'name' => 'Transaksi',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $menus = [
            [$masterId, 'Master Karyawan', 'employees.index', 'fas fa-users', 20],
            [$masterId, 'Pembuatan ID', 'employee_transactions.id_creation', 'fas fa-id-card', 21],
            [$transactionId, 'Perubahan ID Sistem', 'employee_transactions.system_id', 'fas fa-right-left', 20],
            [$transactionId, 'Pengunduran Diri', 'employee_transactions.resignation', 'fas fa-user-minus', 21],
            [$transactionId, 'Mutasi Posisi', 'employee_transactions.position_mutation', 'fas fa-briefcase', 22],
            [$transactionId, 'Mutasi Drop Point', 'employee_transactions.droppoint_mutation', 'fas fa-location-dot', 23],
        ];

        foreach ($menus as [$groupId, $name, $route, $icon, $order]) {
            if (! DB::table('menus')->where('menu_group_id', $groupId)->where('name', $name)->exists()) {
                DB::table('menus')->insert([
                    'menu_group_id' => $groupId,
                    'name' => $name,
                    'route' => $route,
                    'icon' => $icon,
                    'sort_order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('menus')->whereIn('name', [
            'Master Karyawan',
            'Pembuatan ID',
            'Perubahan ID Sistem',
            'Pengunduran Diri',
            'Mutasi Posisi',
            'Mutasi Drop Point',
        ])->delete();
    }
};
