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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');   // PC/Komputer atau CCTV
            $table->string('nama');       // PC Admin, CCTV Koridor, dll
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->text('spesifikasi')->nullable(); // Spesifikasi hardware/fitur
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
