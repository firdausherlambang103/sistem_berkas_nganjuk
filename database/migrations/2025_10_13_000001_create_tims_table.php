<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PENGAMAN: Hanya buat tabel 'tims' jika belum ada di database
        if (!Schema::hasTable('tims')) {
            Schema::create('tims', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tim')->unique();
                $table->string('nomor_sk')->nullable(); // Kolom ini dibuat disini
                $table->date('tanggal_sk')->nullable(); // Kolom ini dibuat disini
                $table->timestamps();
            });
        }

        // PENGAMAN: Hanya buat tabel pivot jika belum ada
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