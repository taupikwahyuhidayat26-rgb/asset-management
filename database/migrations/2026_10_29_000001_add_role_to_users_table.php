<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('User')->after('password');
        });

        $adminEmail = config('access.initial_admin_email');
        if ($adminEmail) {
            DB::table('users')
                ->whereRaw('LOWER(email) = ?', [strtolower($adminEmail)])
                ->update(['role' => 'Admin']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
