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
        Schema::create('wa_placeholders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();      // Kode placeholder, misal: {nama_pemohon}
            $table->string('description');         // Deskripsi, misal: Nama Lengkap Pemohon
            $table->string('example')->nullable(); // Contoh data, misal: Budi Santoso
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_placeholders');
    }
};