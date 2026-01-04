<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerima_kuasas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kuasa')->unique(); // Kode unik untuk identifikasi
            $table->string('nama_kuasa');
            $table->string('nomer_wa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerima_kuasas');
    }
};