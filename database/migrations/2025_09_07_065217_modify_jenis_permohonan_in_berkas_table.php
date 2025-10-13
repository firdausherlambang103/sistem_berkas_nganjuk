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
            // Hapus kolom lama jika ada. Tambahkan _id untuk foreign key
            $table->dropColumn('jenis_permohonan');
            $table->foreignId('jenis_permohonan_id')->after('nomer_hak')->constrained('jenis_permohonans');
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
