<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('peminjaman_buku_tanahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Petugas yang input
            $table->string('nomor_berkas')->nullable(); // Opsional
            
            // Data Buku Tanah
            $table->string('jenis_hak'); // HM, HGB, HP, dll
            $table->string('nomor_hak');
            $table->foreignId('desa_id')->constrained('desas');
            $table->foreignId('kecamatan_id')->constrained('kecamatans');
            
            // Status & Catatan
            $table->enum('status', [
                'Ditemukan', 
                'Surat Tugas 1', 
                'Surat Tugas 2', 
                'Buku Tanah Pengganti', 
                'Blokir'
            ]);
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('peminjaman_buku_tanahs');
    }
};