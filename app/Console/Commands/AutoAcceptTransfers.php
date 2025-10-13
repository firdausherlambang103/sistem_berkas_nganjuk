<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Berkas;
use App\Models\RiwayatBerkas;
use Carbon\Carbon;

class AutoAcceptTransfers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'berkas:accept-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menerima secara otomatis berkas yang dikirim lebih dari dua jam yang lalu.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai memeriksa berkas terkirim yang terlambat diterima...');

        // Tentukan batas waktu (2 jam yang lalu)
        $limitTime = Carbon::now()->subHours(2);

        // 1. Cari semua berkas yang statusnya 'Dikirim' dan sudah melewati batas waktu
        $berkasToAccept = Berkas::where('status_pengiriman', 'Dikirim')
                                ->where('updated_at', '<=', $limitTime)
                                ->get();

        if ($berkasToAccept->isEmpty()) {
            $this->info('Tidak ada berkas yang perlu diterima otomatis.');
            return 0; // Berhenti jika tidak ada yang perlu diperbaiki
        }

        $this->info("Ditemukan " . $berkasToAccept->count() . " berkas yang akan diterima secara otomatis.");

        $bar = $this->output->createProgressBar($berkasToAccept->count());
        $bar->start();

        foreach ($berkasToAccept as $berkas) {
            $penerimaId = $berkas->penerima_id;

            // 2. Update status berkas seperti saat diterima manual
            $berkas->status_pengiriman = 'Diterima';
            $berkas->posisi_sekarang_user_id = $penerimaId;
            $berkas->pengirim_id = null;
            $berkas->penerima_id = null;
            $berkas->save();

            // 3. Buat catatan riwayat untuk transparansi
            RiwayatBerkas::create([
                'berkas_id' => $berkas->id,
                'dari_user_id' => $penerimaId, // Dicatat seolah-olah penerima yang melakukan aksi
                'ke_user_id' => $penerimaId,
                'waktu_kirim' => now(),
                'catatan_pengiriman' => 'Berkas diterima secara otomatis oleh sistem (lebih dari 2 jam).',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nProses penerimaan otomatis selesai.");

        return 0;
    }
}
