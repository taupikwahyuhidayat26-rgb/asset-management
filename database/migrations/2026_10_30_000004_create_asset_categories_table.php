<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $now = now();
        $existingCategories = DB::table('assets')->distinct()->pluck('kategori');
        foreach ($existingCategories->merge(['PC/Komputer', 'CCTV'])->filter()->unique() as $name) {
            DB::table('asset_categories')->insertOrIgnore([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->addNavigationEntry($now);
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->where('route', 'categories.index')->delete();
        }

        Schema::dropIfExists('asset_categories');
    }

    private function addNavigationEntry(Carbon $now): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_groups')) {
            return;
        }

        $groupId = DB::table('menu_groups')->where('name', 'Master Data')->value('id');
        if (! $groupId) {
            $groupId = DB::table('menu_groups')->insertGetId([
                'name' => 'Master Data',
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('menus')->updateOrInsert(
            ['menu_group_id' => $groupId, 'route' => 'categories.index'],
            [
                'name' => 'Kategori Aset',
                'icon' => 'fas fa-tags',
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('menus')
            ->where('menu_group_id', $groupId)
            ->where('route', 'supplier.index')
            ->update(['sort_order' => 4, 'updated_at' => $now]);
    }
};
