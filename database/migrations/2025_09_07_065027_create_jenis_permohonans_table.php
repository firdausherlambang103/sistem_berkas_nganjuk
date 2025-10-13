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
        Schema::create('jenis_permohonans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_permohonan')->unique();
            $table->integer('waktu_timeline_hari'); // Durasi timeline dalam hari
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_permohonans');
    }
};
