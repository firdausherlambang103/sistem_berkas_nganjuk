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
        // Kolom untuk melacak status pengiriman
        $table->enum('status_pengiriman', ['Diterima', 'Dikirim'])->default('Diterima')->after('status');
        
        // Kolom untuk mencatat siapa pengirim terakhir
        $table->foreignId('pengirim_id')->nullable()->after('status_pengiriman')->constrained('users');
        
        // Kolom untuk mencatat siapa calon penerima
        $table->foreignId('penerima_id')->nullable()->after('pengirim_id')->constrained('users');
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
