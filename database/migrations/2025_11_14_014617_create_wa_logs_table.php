<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel berkas (agar tahu log ini untuk berkas mana)
            $table->foreignId('berkas_id')->constrained('berkas')->onDelete('cascade');
            
            // Relasi ke user (siapa yang mengirim)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('target_phone');
            $table->text('pesan');
            $table->string('status')->default('terkirim'); // Contoh: 'sukses', 'gagal'
            $table->text('keterangan')->nullable(); // Untuk menyimpan pesan error jika gagal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_logs');
    }
};