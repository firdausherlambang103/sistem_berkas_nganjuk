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
        Schema::create('jadwal_ukur', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke berkas yang diukur
            $table->foreignId('berkas_id')->constrained('berkas')->onDelete('cascade');
            // Menghubungkan ke petugas ukur yang ditugaskan
            $table->foreignId('petugas_ukur_id')->constrained('petugas_ukur')->onDelete('cascade');
            $table->string('no_surat_tugas')->nullable();
            $table->date('tanggal_rencana_ukur')->nullable();
            $table->string('status_proses')->default('Terjadwal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ukur');
    }
};
