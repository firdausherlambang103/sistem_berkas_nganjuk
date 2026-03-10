<?php

namespace App\Models;

// Import SoftDeletes agar tidak error "Class not found"
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;

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
        'nomer_wa',
        'password',
        'jabatan_id',
        'is_approved',
        'akses_menu', // [BARU] Tambahkan ini agar bisa disimpan ke database
        'akses_layer',
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
        'akses_menu' => 'array', // [BARU] Ubah otomatis dari JSON ke Array PHP
        'akses_layer' => 'array',
    ];

    // [BARU] Fungsi untuk mengecek hak akses menu/fitur dari checkbox Admin
    public function hasMenuAccess($menu)
    {
        // Admin selalu punya akses penuh
        if ($this->jabatan && $this->jabatan->is_admin) {
            return true;
        }

        // Cek apakah menu ada di dalam array akses_menu user
        $akses = $this->akses_menu ?? [];
        return in_array($menu, $akses);
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

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
     */
    public function berkasDiTangan()
    {
        return $this->hasMany(Berkas::class, 'posisi_sekarang_user_id')
                    ->where('status', '!=', 'Ditutup');
    }

    /**
     * Relasi: Riwayat berkas yang pernah DITERIMA user.
     */
    public function riwayatDiterima()
    {
        return $this->hasMany(RiwayatBerkas::class, 'ke_user_id');
    }

    /**
     * Relasi: Riwayat berkas yang pernah DIKIRIM user.
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