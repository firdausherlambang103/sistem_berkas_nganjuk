<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_jabatan',
        'is_admin',
    ];

    /**
     * Mendefinisikan relasi bahwa satu Jabatan bisa dimiliki oleh banyak User.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

        // Baris ini memastikan model terhubung ke tabel 'jabatans'
    protected $table = 'jabatans';
}
