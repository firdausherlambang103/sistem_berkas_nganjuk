    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('berkas', function (Blueprint $table) {
                // Menghubungkan berkas ke user yang ditunjuk sebagai petugas ukur
                $table->foreignId('petugas_ukur_id')->nullable()->constrained('users')->onDelete('set null')->after('penerima_id');
                $table->date('tanggal_ukur')->nullable()->after('petugas_ukur_id');
            });
        }

        public function down(): void
        {
            Schema::table('berkas', function (Blueprint $table) {
                $table->dropForeign(['petugas_ukur_id']);
                $table->dropColumn(['petugas_ukur_id', 'tanggal_ukur']);
            });
        }
    };