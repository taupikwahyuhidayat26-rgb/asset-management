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
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            
            // PASTIKAN TULISANNYA 'asset_id' BUKAN 'assets_id'
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            $table->string('sku')->unique();
            $table->string('serial_number')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status')->default('Ready');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};
