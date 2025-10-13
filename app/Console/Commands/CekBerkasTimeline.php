<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Berkas;
use Carbon\Carbon;
use App\Notifications\BerkasMelebihiTimeline;

class CekBerkasTimeline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cek-berkas-timeline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai memeriksa berkas yang melebihi timeline...');

        // Ambil semua berkas yang masih diproses, beserta relasinya
        $berkasDiproses = Berkas::where('status', 'Diproses')->with(['jenisPermohonan', 'posisiSekarang'])->get();

        foreach ($berkasDiproses as $berkas) {
            // Hitung sudah berapa hari berkas ini di posisi sekarang
            $hariDiPosisiSekarang = Carbon::now()->diffInDays($berkas->updated_at);
            $batasWaktu = $berkas->jenisPermohonan->waktu_timeline_hari;

            if ($hariDiPosisiSekarang > $batasWaktu) {
                // Jika melebihi batas, kirim notifikasi ke user yang memegang berkas
                $userYangMemegang = $berkas->posisiSekarang;
                $userYangMemegang->notify(new BerkasMelebihiTimeline($berkas));

                $this->warn("Notifikasi dikirim untuk berkas: {$berkas->nomer_berkas}");
            }
        }

        $this->info('Pemeriksaan selesai.');
    }
}
