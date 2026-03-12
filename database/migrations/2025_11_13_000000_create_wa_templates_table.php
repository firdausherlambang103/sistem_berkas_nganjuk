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
        // Nonaktifkan pemeriksaan foreign key sementara
        Schema::disableForeignKeyConstraints();

        // Hapus tabel jika sudah ada (Force Reset untuk tabel ini)
        Schema::dropIfExists('wa_templates');

        // Buat tabel ulang
        Schema::create('wa_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');     
            $table->text('template');   
            $table->string('status')->default('aktif'); 
            $table->timestamps();
        });

        // Aktifkan kembali pemeriksaan foreign key
        Schema::enableForeignKeyConstraints();
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