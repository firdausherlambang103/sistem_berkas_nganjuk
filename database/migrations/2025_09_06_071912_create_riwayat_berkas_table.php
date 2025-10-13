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
        Schema::create('riwayat_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_id')->constrained('berkas')->onDelete('cascade'); // Jika berkas dihapus, riwayatnya ikut terhapus
            $table->foreignId('dari_user_id')->constrained('users');
            $table->foreignId('ke_user_id')->constrained('users');
            $table->timestamp('waktu_kirim');
            $table->text('catatan_pengiriman')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_berkas');
    }
};
