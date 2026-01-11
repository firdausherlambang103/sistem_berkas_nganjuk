<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Mengubah kolom status agar bisa menampung 'Dikembalikan'
        // Perintah DB::statement ini khusus MySQL. 
        // Pastikan daftar enum mencakup status lama + status baru 'Dikembalikan'
        DB::statement("ALTER TABLE peminjaman_buku_tanahs MODIFY COLUMN status ENUM('Ditemukan', 'Surat Tugas 1', 'Surat Tugas 2', 'Buku Tanah Pengganti', 'Blokir', 'Dikembalikan', 'Warkah') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_buku_tanahs', function (Blueprint $table) {
            //
        });
    }
};
