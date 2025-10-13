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
        Schema::table('berkas', function (Blueprint $table) {
            // Waktu saat timer perhitungan dimulai
            $table->timestamp('waktu_mulai_proses')->nullable()->after('status');
            // Waktu saat berkas ditandai Selesai
            $table->timestamp('waktu_selesai_proses')->nullable()->after('waktu_mulai_proses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berkas', function (Blueprint $table) {
            //
        });
    }
};
