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
        // Nonaktifkan foreign key checks sementara agar bisa drop tabel yang direlasikan
        Schema::disableForeignKeyConstraints();
        
        // Hapus tabel lama jika ada
        Schema::dropIfExists('wa_templates');
        
        // Aktifkan kembali foreign key checks
        Schema::enableForeignKeyConstraints();

        Schema::create('wa_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('template');
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('wa_templates');
        Schema::enableForeignKeyConstraints();
    }
};