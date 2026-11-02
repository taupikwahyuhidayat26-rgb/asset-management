<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('no_karyawan')->unique();
            $table->string('nama_karyawan');
            $table->string('posisi_1')->nullable();
            $table->string('posisi_2')->nullable();
            $table->string('status_karyawan')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('masa_kerja')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('nik_ktp')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('bca')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('status_kerja')->default('Aktif');
            $table->foreignId('droppoint_id')->nullable()->constrained('droppoint')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->string('id_sebelumnya')->nullable();
            $table->string('nama_id_sebelumnya')->nullable();
            $table->timestamps();
        });

        Schema::create('id_creation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->string('status_pemrosesan')->default('Menunggu');
            $table->timestamps();
        });

        Schema::create('system_id_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('id_aktif');
            $table->string('id_sebelumnya')->nullable();
            $table->date('tanggal_update');
            $table->string('status_pemrosesan')->default('Menunggu');
            $table->timestamps();
        });

        Schema::create('employee_resignations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('tanggal_resign');
            $table->text('alasan')->nullable();
            $table->string('status_pemrosesan')->default('Menunggu');
            $table->timestamps();
        });

        Schema::create('employee_position_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('posisi_1_lama')->nullable();
            $table->string('posisi_2_lama')->nullable();
            $table->string('status_karyawan_lama')->nullable();
            $table->foreignId('droppoint_lama_id')->nullable()->constrained('droppoint')->nullOnDelete();
            $table->string('posisi_baru');
            $table->string('status_karyawan_baru')->nullable();
            $table->date('tanggal_mutasi');
            $table->text('keterangan')->nullable();
            $table->string('status_pemrosesan')->default('Menunggu');
            $table->timestamps();
        });

        Schema::create('employee_droppoint_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('droppoint_lama_id')->nullable()->constrained('droppoint')->nullOnDelete();
            $table->date('tanggal_mutasi_dp');
            $table->foreignId('droppoint_baru_id')->constrained('droppoint')->restrictOnDelete();
            $table->string('status_pemrosesan')->default('Menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_droppoint_mutations');
        Schema::dropIfExists('employee_position_mutations');
        Schema::dropIfExists('employee_resignations');
        Schema::dropIfExists('system_id_changes');
        Schema::dropIfExists('id_creation_requests');
        Schema::dropIfExists('employee_systems');
        Schema::dropIfExists('employees');
    }
};
