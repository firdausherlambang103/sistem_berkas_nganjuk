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
        Schema::create('berkas', function (Blueprint $table) {
                $table->id(); // Kolom nomor urut otomatis (Primary Key)
                $table->string('nomer_berkas')->unique(); // Nomer berkas, harus unik
                $table->string('nama_pemohon');
                $table->string('jenis_alas_hak');
                $table->string('nomer_hak');
                $table->string('kecamatan');
                $table->string('desa');
                $table->string('jenis_permohonan');
                $table->string('nomer_wa')->nullable();
                $table->text('catatan')->nullable(); // Catatan bisa panjang
                $table->foreignId('posisi_sekarang_user_id')->constrained('users'); // Penghubung ke tabel users
                $table->enum('status', ['Diproses', 'Selesai', 'Ditutup', 'Pending'])->default('Diproses');
                $table->timestamps(); // Kolom created_at dan updated_at otomatis
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
