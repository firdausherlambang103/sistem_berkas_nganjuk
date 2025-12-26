<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Jabatan;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_approved',
        'jabatan_id', // <-- TAMBAHKAN INI
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function jabatan()
    {
        // Pastikan parameter kedua adalah 'jabatan_id'
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Relasi untuk mendapatkan berkas yang sedang dipegang user.
     */
    public function berkasDiTangan()
    {
        return $this->hasMany(Berkas::class, 'posisi_sekarang_user_id');
    }

    public function petugasUkur()
    {
        return $this->hasOne(PetugasUkur::class, 'user_id');
    }

    // Relasi untuk menghitung Berkas Masuk (User sebagai Penerima)
    public function riwayatDiterima()
    {
        return $this->hasMany(RiwayatBerkas::class, 'ke_user_id');
    }

    // Relasi untuk menghitung Berkas Keluar (User sebagai Pengirim)
    public function riwayatDikirim()
    {
        return $this->hasMany(RiwayatBerkas::class, 'dari_user_id');
    }
}
