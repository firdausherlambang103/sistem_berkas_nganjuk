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
        // Hapus tabel jika sudah ada (Force Reset untuk tabel ini)
        Schema::dropIfExists('wa_templates');

        Schema::create('wa_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');     // Menggunakan 'nama' bukan 'judul'
            $table->text('template');   // Menggunakan 'template' bukan 'pesan'
            $table->string('status')->default('aktif'); // Status: aktif/tidak_aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_templates');
    }
};