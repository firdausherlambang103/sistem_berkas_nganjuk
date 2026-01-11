<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah status_buku_tanah di tabel berkas
        Schema::table('berkas', function (Blueprint $table) {
            // Ada = Buku tanah sudah dipegang / tidak perlu pinjam
            // Butuh = Perlu meminjam dari arsip
            $table->enum('status_buku_tanah', ['Ada', 'Butuh'])->default('Ada')->after('status');
        });

        // 2. Tambahkan kolom berkas_id di tabel peminjaman agar ter-link secara sistem (relasi kuat)
        Schema::table('peminjaman_buku_tanahs', function (Blueprint $table) {
            $table->unsignedBigInteger('berkas_id')->nullable()->after('user_id');
            // Opsional: Foreign key (jika ingin strict)
            // $table->foreign('berkas_id')->references('id')->on('berkas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('berkas', function (Blueprint $table) {
            $table->dropColumn('status_buku_tanah');
        });
        
        Schema::table('peminjaman_buku_tanahs', function (Blueprint $table) {
            $table->dropColumn('berkas_id');
        });
    }
};