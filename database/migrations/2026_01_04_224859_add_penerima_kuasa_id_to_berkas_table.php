<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('berkas', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada agar tidak error
            if (!Schema::hasColumn('berkas', 'penerima_kuasa_id')) {
                // Tambahkan kolom baru
                // Pastikan 'penerima_kuasas' sesuai nama tabel yang Anda buat di migrasi sebelumnya
                $table->foreignId('penerima_kuasa_id')
                      ->nullable()
                      ->constrained('penerima_kuasas')
                      ->onDelete('set null'); 
            }
        });
    }

    public function down(): void
    {
        Schema::table('berkas', function (Blueprint $table) {
            $table->dropForeign(['penerima_kuasa_id']);
            $table->dropColumn('penerima_kuasa_id');
        });
    }
};