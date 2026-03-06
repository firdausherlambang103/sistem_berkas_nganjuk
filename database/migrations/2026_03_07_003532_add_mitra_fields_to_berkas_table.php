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
        Schema::table('berkas', function (Blueprint $table) {
            $table->string('file_sertipikat')->nullable()->after('status_buku_tanah');
            $table->string('file_data_pendukung')->nullable()->after('file_sertipikat');
            $table->decimal('latitude', 10, 7)->nullable()->after('file_data_pendukung');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        Schema::table('berkas', function (Blueprint $table) {
            $table->dropColumn(['file_sertipikat', 'file_data_pendukung', 'latitude', 'longitude']);
        });
    }
};
