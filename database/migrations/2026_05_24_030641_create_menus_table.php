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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            // Menghubungkan menu ke grupnya
            $table->foreignId('menu_group_id')->constrained('menu_groups')->onDelete('cascade');
            $table->string('name'); // Nama menu: Master Inventory, Supplier, dll.
            $table->string('route'); // Nama route Laravel: asset.index, supplier.index, dll.
            $table->string('icon'); // Kelas ikon FontAwesome: fas fa-boxes, dll.
            $table->integer('sort_order')->default(0); // Mengatur urutan item di dalam grup
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
