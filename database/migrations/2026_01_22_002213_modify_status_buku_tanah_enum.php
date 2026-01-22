<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // LANGKAH 1: Ubah dulu ke VARCHAR agar tidak error "Data Truncated" saat konversi
        DB::statement("ALTER TABLE berkas MODIFY COLUMN status_buku_tanah VARCHAR(50)");

        // LANGKAH 2: Migrasi Data Lama ke Data Baru
        // Logika: 
        // 'Butuh' (Perlu pinjam arsip) -> Menjadi 'Sertipikat Analog'
        // 'Ada' (Sudah ada/tidak butuh) -> Kita asumsikan menjadi 'Sertipikat Elektronik' (atau default lain sesuai kebutuhan)
        
        DB::table('berkas')
            ->where('status_buku_tanah', 'Butuh')
            ->update(['status_buku_tanah' => 'Sertipikat Analog']);

        DB::table('berkas')
            ->where('status_buku_tanah', 'Ada')
            ->update(['status_buku_tanah' => 'Sertipikat Elektronik']);
            
        // Handle data kosong/null jika ada
        DB::table('berkas')
            ->whereNull('status_buku_tanah')
            ->orWhere(function($query) {
                $query->where('status_buku_tanah', '!=', 'Sertipikat Elektronik')
                      ->where('status_buku_tanah', '!=', 'Sertipikat Analog');
            })
            ->update(['status_buku_tanah' => 'Belum Sertipikat']);

        // LANGKAH 3: Ubah ke ENUM baru setelah semua data bersih
        DB::statement("ALTER TABLE berkas MODIFY COLUMN status_buku_tanah ENUM('Sertipikat Elektronik', 'Sertipikat Analog', 'Belum Sertipikat') DEFAULT 'Belum Sertipikat'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke VARCHAR dulu
        DB::statement("ALTER TABLE berkas MODIFY COLUMN status_buku_tanah VARCHAR(50)");

        // Kembalikan Data Baru ke Data Lama (Mapping balik)
        DB::table('berkas')
            ->where('status_buku_tanah', 'Sertipikat Analog')
            ->update(['status_buku_tanah' => 'Butuh']);

        // Sisanya ('Sertipikat Elektronik' & 'Belum Sertipikat') kembalikan ke 'Ada'
        DB::table('berkas')
            ->where('status_buku_tanah', '!=', 'Butuh')
            ->update(['status_buku_tanah' => 'Ada']);

        // Kembalikan ke definisi ENUM lama
        DB::statement("ALTER TABLE berkas MODIFY COLUMN status_buku_tanah ENUM('Ada', 'Butuh') DEFAULT 'Ada'");
    }
};