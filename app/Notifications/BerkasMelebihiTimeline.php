<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Berkas;

class BerkasMelebihiTimeline extends Notification
{
    use Queueable;
    public $berkas;

    public function __construct(Berkas $berkas)
    {
        $this->berkas = $berkas;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Simpan notifikasi ke database
    }

    // Data yang akan disimpan di kolom 'data' pada tabel notifikasi
    public function toArray(object $notifiable): array
    {
        return [
            'berkas_id' => $this->berkas->id,
            'nomer_berkas' => $this->berkas->nomer_berkas,
            'message' => "Perhatian: Berkas {$this->berkas->nomer_berkas} telah melebihi batas waktu di meja Anda!",
        ];
    }
}
