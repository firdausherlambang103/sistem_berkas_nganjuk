<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Menambahkan kolom seksi setelah nama_jabatan, nullable (opsional) agar data lama aman
            $table->string('seksi')->nullable()->after('nama_jabatan');
        });
    }

    public function down()
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropColumn('seksi');
        });
    }
};
