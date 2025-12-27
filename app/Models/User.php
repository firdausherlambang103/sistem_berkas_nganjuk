<?php

namespace App\Models;

// Import SoftDeletes agar tidak error "Class not found"
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan_id',
        'is_approved',
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
        'is_approved' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI (RELATIONSHIPS)
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke Jabatan
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    /**
     * Relasi: Berkas yang sedang dipegang user saat ini.
     * Digunakan di LaporanController untuk menghitung 'sisa_berkas'.
     * KOREKSI: Kolom foreign key adalah 'posisi_sekarang_user_id', BUKAN 'user_id'.
     * MODIFIKASI: Filter agar berkas yang 'Ditutup' tidak muncul.
     */
    public function berkasDiTangan()
    {
        return $this->hasMany(Berkas::class, 'posisi_sekarang_user_id')
                    ->where('status', '!=', 'Ditutup');
    }

    /**
     * Relasi: Riwayat berkas yang pernah DITERIMA user.
     * Digunakan di LaporanController untuk menghitung 'total_masuk'.
     */
    public function riwayatDiterima()
    {
        return $this->hasMany(RiwayatBerkas::class, 'ke_user_id');
    }

    /**
     * Relasi: Riwayat berkas yang pernah DIKIRIM user.
     * Digunakan di LaporanController untuk menghitung 'total_keluar'.
     */
    public function riwayatDikirim()
    {
        return $this->hasMany(RiwayatBerkas::class, 'dari_user_id');
    }
    
    /**
     * Relasi: Tim dimana user menjadi anggota.
     */
    public function tims()
    {
        return $this->belongsToMany(Tim::class, 'tim_user');
    }
}