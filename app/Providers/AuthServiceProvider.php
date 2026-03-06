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

        /**
         * Mendefinisikan Gate 'create-berkas'.
         * Gate ini mengizinkan: Admin, Petugas Loket, ATAU user yang diberi akses 'buat_berkas' oleh admin.
         */
        Gate::define('create-berkas', function (User $user) {
            return (optional($user->jabatan)->is_admin) || 
                   str_starts_with(optional($user->jabatan)->nama_jabatan ?? '', 'Petugas Loket') || 
                   $user->hasMenuAccess('buat_berkas');
        });

        /**
         * GATE BARU: Gate untuk user yang boleh mengelola (edit/hapus) berkas.
         * Hanya user dengan jabatan Admin / 'Administrator' yang diizinkan.
         */
        Gate::define('manage-berkas', function (User $user) {
            return optional($user->jabatan)->is_admin || optional($user->jabatan)->nama_jabatan === 'Administrator';
        });
    }
}