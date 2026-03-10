<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('berkas', function (Blueprint $table) {
            $table->date('tgl_bayar')->nullable();
            $table->string('penerima_kwitansi')->nullable();
            $table->date('tgl_penyerahan_kwitansi')->nullable();
        });
    }

    public function down()
    {
        Schema::table('berkas', function (Blueprint $table) {
            $table->dropColumn(['tgl_bayar', 'penerima_kwitansi', 'tgl_penyerahan_kwitansi']);
        });
    }
};