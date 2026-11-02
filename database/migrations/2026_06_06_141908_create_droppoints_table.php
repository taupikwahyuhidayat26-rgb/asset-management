<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan nama tabelnya 'droppoint' (tanpa 's' di belakang agar sesuai dengan permintaanmu)
        Schema::create('droppoint', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dp', 50)->unique(); // Dibuat unique agar kode tidak boleh kembar
            $table->string('nama_dp');
            $table->string('nama_cps')->nullable(); // Boleh kosong jika sementara belum ada penanggung jawab
            $table->string('posisi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('droppoints');
    }
};
