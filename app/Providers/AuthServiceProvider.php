<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User; 

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
         $this->registerPolicies();

        // --- TAMBAHKAN KODE DI BAWAH INI ---

        /**
         * Mendefinisikan Gate 'create-berkas'.
         * Gate ini akan mengembalikan true HANYA JIKA jabatan user adalah 'Loket'.
         */
        Gate::define('create-berkas', function (User $user) {
            // Kita gunakan optional() untuk mencegah error jika user belum punya jabatan
            return str_starts_with(optional($user->jabatan)->nama_jabatan ?? '', 'Petugas Loket');
        });

        /**
         * GATE BARU: Gate untuk user yang boleh mengelola (edit/hapus) berkas.
         * Hanya user dengan jabatan 'Administrator' yang diizinkan.
         */
        Gate::define('manage-berkas', function (User $user) {
            return optional($user->jabatan)->nama_jabatan === 'Administrator';
        });
    }
}
