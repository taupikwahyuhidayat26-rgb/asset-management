<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_groups')) {
            return;
        }

        $now = now();
        $groupId = DB::table('menu_groups')->where('name', 'Laporan')->value('id');
        if (! $groupId) {
            $groupId = DB::table('menu_groups')->insertGetId([
                'name' => 'Laporan',
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('menus')->updateOrInsert(
            ['menu_group_id' => $groupId, 'route' => 'inventory_report.index'],
            [
                'name' => 'Laporan Inventory',
                'icon' => 'fas fa-file-csv text-success',
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->where('route', 'inventory_report.index')->delete();
        }
    }
};
