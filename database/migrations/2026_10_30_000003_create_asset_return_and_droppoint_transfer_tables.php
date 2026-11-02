<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asset_returns')) {
            Schema::create('asset_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_code')->unique();
                $table->date('return_date');
                $table->foreignId('source_droppoint_id')->constrained('droppoint')->restrictOnDelete();
                $table->string('destination_status');
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('asset_return_details')) {
            Schema::create('asset_return_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_return_id')->constrained('asset_returns')->cascadeOnDelete();
                $table->foreignId('asset_item_id')->constrained()->restrictOnDelete();
                $table->timestamps();
                $table->unique(['asset_return_id', 'asset_item_id']);
            });
        }

        if (! Schema::hasTable('droppoint_transfers')) {
            Schema::create('droppoint_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_code')->unique();
                $table->date('transfer_date');
                $table->foreignId('source_droppoint_id')->constrained('droppoint')->restrictOnDelete();
                $table->foreignId('destination_droppoint_id')->constrained('droppoint')->restrictOnDelete();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('droppoint_transfers', 'dp_transfers_source_dest_idx')) {
            Schema::table('droppoint_transfers', function (Blueprint $table) {
                $table->index(
                    ['source_droppoint_id', 'destination_droppoint_id'],
                    'dp_transfers_source_dest_idx'
                );
            });
        }

        if (! Schema::hasTable('droppoint_transfer_details')) {
            Schema::create('droppoint_transfer_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('droppoint_transfer_id')->constrained('droppoint_transfers')->cascadeOnDelete();
                $table->foreignId('asset_item_id')->constrained()->restrictOnDelete();
                $table->timestamps();
                $table->unique(['droppoint_transfer_id', 'asset_item_id']);
            });
        }

        $this->addNavigationEntries();
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->whereIn('route', [
                'asset_returns.index',
                'droppoint_transfers.index',
            ])->delete();
        }
        Schema::dropIfExists('droppoint_transfer_details');
        Schema::dropIfExists('droppoint_transfers');
        Schema::dropIfExists('asset_return_details');
        Schema::dropIfExists('asset_returns');
    }

    private function addNavigationEntries(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_groups')) {
            return;
        }

        $now = now();
        $groupId = DB::table('menu_groups')->where('name', 'Transaksi')->value('id');
        if (! $groupId) {
            $groupId = DB::table('menu_groups')->insertGetId([
                'name' => 'Transaksi',
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        if (! $groupId) {
            return;
        }

        foreach ([
            [
                'name' => 'Barang Kembali',
                'route' => 'asset_returns.index',
                'icon' => 'fas fa-rotate-left text-info',
                'sort_order' => 3,
            ],
            [
                'name' => 'Transfer Drop Point',
                'route' => 'droppoint_transfers.index',
                'icon' => 'fas fa-right-left text-warning',
                'sort_order' => 4,
            ],
        ] as $menu) {
            DB::table('menus')->updateOrInsert(
                ['menu_group_id' => $groupId, 'route' => $menu['route']],
                [...$menu, 'menu_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
};
