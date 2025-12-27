<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PERBAIKAN: Cek dulu apakah tabel 'tims' sudah ada?
        // Jika BELUM ada (!), baru buat. Jika sudah ada, lewati.
        if (!Schema::hasTable('tims')) {
            Schema::create('tims', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tim')->unique();
                $table->string('nomor_sk')->nullable();
                $table->date('tanggal_sk')->nullable();
                $table->timestamps();
            });
        }

        // PERBAIKAN: Cek dulu apakah tabel pivot 'tim_user' sudah ada?
        if (!Schema::hasTable('tim_user')) {
            Schema::create('tim_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tim_id')->constrained('tims')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_user');
        Schema::dropIfExists('tims');
    }
};