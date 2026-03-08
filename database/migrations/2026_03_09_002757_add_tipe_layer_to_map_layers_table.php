<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('map_layers', function (Blueprint $table) {
            $table->string('tipe_layer')->nullable()->after('nama_layer');
        });
    }

    public function down()
    {
        Schema::table('map_layers', function (Blueprint $table) {
            $table->dropColumn('tipe_layer');
        });
    }
};