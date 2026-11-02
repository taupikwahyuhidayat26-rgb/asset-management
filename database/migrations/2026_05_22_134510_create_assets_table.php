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
            $table->string('sku')->unique();
            $table->string('nama');
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->text('spesifikasi')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status')->default('Ready');
            $table->string('kategori'); // CCTV atau PC/Komputer
            $table->text('keterangan')->nullable();
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
