<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RiwayatBerkas;
use App\Models\Berkas;
use Carbon\Carbon;

class AutoAcceptBerkas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'berkas:auto-accept';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menerima secara otomatis berkas yang pending lebih dari 24 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai memeriksa berkas yang pending...');

        // Tentukan batas waktu (24 jam yang lalu)
        $batasWaktu = Carbon::now()->subDay();

        // 1. Cari semua riwayat pengiriman yang masih 'Pending' dan sudah lewat 24 jam
        $pengirimanTerlambat = RiwayatBerkas::where('status_penerimaan', 'Pending')
                                            ->where('waktu_kirim', '<=', $batasWaktu)
                                            ->get();

        if ($pengirimanTerlambat->isEmpty()) {
            $this->info('Tidak ada berkas pending yang perlu diterima otomatis.');
            return;
        }

        $this->info("Ditemukan {$pengirimanTerlambat->count()} berkas untuk diterima otomatis.");

        foreach ($pengirimanTerlambat as $pengiriman) {
            // 2. Update status pengiriman menjadi 'Diterima'
            $pengiriman->status_penerimaan = 'Diterima';
            $pengiriman->save();

            // 3. Update posisi berkas utama ke user penerima
            $berkas = Berkas::find($pengiriman->berkas_id);
            if ($berkas) {
                $berkas->posisi_sekarang_user_id = $pengiriman->ke_user_id;
                $berkas->save();
                $this->info("Berkas #{$berkas->nomer_berkas} berhasil diterima oleh user ID: {$pengiriman->ke_user_id}");
            }
        }

        $this->info('Proses penerimaan otomatis selesai.');
    }
}