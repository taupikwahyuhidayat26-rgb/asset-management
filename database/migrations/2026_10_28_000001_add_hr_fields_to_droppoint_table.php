<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('droppoint', function (Blueprint $table) {
            $table->string('rm')->nullable();
            $table->string('spv')->nullable();
            $table->string('area_cover')->nullable();
            $table->string('status_dp')->default('Aktif');
        });
    }

    public function down(): void
    {
        Schema::table('droppoint', function (Blueprint $table) {
            $table->dropColumn(['rm', 'spv', 'area_cover', 'status_dp']);
        });
    }
};
