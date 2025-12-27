<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat Tabel Tim
        Schema::create('tims', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tim')->unique();
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->timestamps();
        });

        // 2. Buat Tabel Pivot (Hubungan Tim <-> User)
        Schema::create('tim_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tim_id')->constrained('tims')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_user');
        Schema::dropIfExists('tims');
    }
};