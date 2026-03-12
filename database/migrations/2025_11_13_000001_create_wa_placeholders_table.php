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
        // Drop jika sudah ada untuk memastikan struktur bersih
        Schema::dropIfExists('wa_placeholders');

        Schema::create('wa_placeholders', function (Blueprint $table) {
            $table->id();
            $table->string('placeholder')->unique();
            $table->string('deskripsi')->nullable();
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