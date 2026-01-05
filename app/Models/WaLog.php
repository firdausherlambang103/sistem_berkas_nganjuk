<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $table = 'wa_logs'; // Pastikan nama tabel benar
    protected $guarded = ['id'];

    public function berkas()
    {
        return $this->belongsTo(Berkas::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}