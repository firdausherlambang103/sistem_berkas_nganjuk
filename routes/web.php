<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuangKerjaController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ManajemenController;
use App\Http\Controllers\Admin\WaTemplateController; // Import Controller WA Baru
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\JadwalUkurController;
use App\Http\Controllers\SuratTugasController;
use App\Models\WaTemplate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Grup rute yang hanya bisa diakses setelah user login
Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD & DETAIL STATISTIK ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/total', [DashboardController::class, 'showTotal'])->name('dashboard.total');
    Route::get('/dashboard/diproses', [DashboardController::class, 'showDiproses'])->name('dashboard.diproses');
    Route::get('/dashboard/selesai', [DashboardController::class, 'showSelesai'])->name('dashboard.selesai');
    Route::get('/dashboard/jatuh-tempo', [DashboardController::class, 'showJatuhTempo'])->name('dashboard.jatuh-tempo');
    
    // --- LAPORAN ---
    Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
        Route::get('/rinci', 'index')->name('index');
        Route::get('/user/{user}', 'showBerkasByUser')->name('berkas_by_user');
    });
    
    // --- PROFIL ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- RUANG KERJA ---
    Route::get('/ruang-kerja', [RuangKerjaController::class, 'index'])->name('ruang-kerja');
    Route::post('/berkas/simpan-kuasa-ajax', [BerkasController::class, 'storeKuasaAjax'])->name('berkas.store-kuasa-ajax');

    // --- API HELPER (Untuk Modal WA) ---
    Route::get('/api/wa-templates', function () {
        return response()->json(WaTemplate::where('is_active', true)->get());
    })->name('api.wa-templates');

    // --- BERKAS (Fungsionalitas Utama) ---
    // Semua URL diawali dengan /berkas dan nama route diawali dengan berkas.
    Route::prefix('berkas')->name('berkas.')->controller(BerkasController::class)->group(function() {
        // Buat Baru
        Route::get('/baru', 'create')->name('create')->middleware('can:create-berkas');
        Route::post('/', 'store')->name('store')->middleware('can:create-berkas');
        
        // Aksi Massal / Spesifik
        Route::post('/kirim', 'kirim')->name('kirim');
        
        // Edit & Update (Akses dibatasi di controller, route ini terbuka untuk auth user dulu)
        Route::get('/{berkas}/edit', 'edit')->name('edit'); 
        Route::put('/{berkas}', 'update')->name('update'); // Pakai PUT/PATCH untuk update
        Route::patch('/{berkas}', 'update'); // Fallback jika form pakai PATCH
        
        // Aksi Status
        Route::post('/{berkas}/terima', 'terima')->name('terima');
        Route::post('/{berkas}/tolak', 'tolak')->name('tolak');
        Route::post('/{berkas}/selesaikan', 'selesaikan')->name('selesaikan');
        Route::post('/{berkas}/tutup', 'tutup')->name('tutup');
        Route::post('/{berkas}/pending', 'pending')->name('pending');
        Route::post('/{berkas}/aktifkan', 'aktifkan')->name('aktifkan');
        
        // Hapus & Detail
        Route::delete('/{berkas}', 'destroy')->name('destroy')->middleware('can:manage-berkas');
        Route::get('/{berkas}', 'show')->name('show');
    });

    // --- PENJADWALAN UKUR ---
    Route::prefix('penjadwalan-ukur')->name('jadwal-ukur.')->controller(JadwalUkurController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/dashboard-petugas', 'dashboardPetugas')->name('dashboard-petugas');
        Route::get('/pilih-petugas/{berkas?}', 'pilihPetugas')->name('pilih-petugas');
        Route::get('/input-jadwal/{petugasUkur}/{berkas?}', 'inputJadwal')->name('input-jadwal');
        Route::post('/simpan-jadwal', 'simpanJadwal')->name('simpan-jadwal');
    });

     // --- PEMBUATAN SURAT TUGAS & BA ---
    Route::prefix('surat-tugas')->name('surat-tugas.')->controller(SuratTugasController::class)->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/generate', 'generate')->name('generate');
    });
});

// --- GRUP ROUTE ADMIN ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // User Approval & Manajemen
    Route::get('/users-approval', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Kuasa
    Route::get('/penerima-kuasa', [ManajemenController::class, 'kuasaIndex'])->name('kuasa.index');
    Route::post('/penerima-kuasa', [ManajemenController::class, 'kuasaStore'])->name('kuasa.store');
    Route::patch('/penerima-kuasa/{kuasa}', [ManajemenController::class, 'kuasaUpdate'])->name('kuasa.update');
    Route::delete('/penerima-kuasa/{kuasa}', [ManajemenController::class, 'kuasaDestroy'])->name('kuasa.destroy');

    // Manajemen Jabatan
    Route::get('/jabatan', [ManajemenController::class, 'jabatanIndex'])->name('jabatan.index');
    Route::post('/jabatan', [ManajemenController::class, 'jabatanStore'])->name('jabatan.store');
    Route::get('/jabatan/{jabatan}/edit', [ManajemenController::class, 'jabatanEdit'])->name('jabatan.edit');
    Route::patch('/jabatan/{jabatan}', [ManajemenController::class, 'jabatanUpdate'])->name('jabatan.update');
    Route::delete('/jabatan/{jabatan}', [ManajemenController::class, 'jabatanDestroy'])->name('jabatan.destroy');

    // Manajemen Kecamatan
    Route::get('/kecamatan', [ManajemenController::class, 'kecamatanIndex'])->name('kecamatan.index');
    Route::post('/kecamatan', [ManajemenController::class, 'kecamatanStore'])->name('kecamatan.store');
    Route::get('/kecamatan/{kecamatan}/edit', [ManajemenController::class, 'kecamatanEdit'])->name('kecamatan.edit');
    Route::patch('/kecamatan/{kecamatan}', [ManajemenController::class, 'kecamatanUpdate'])->name('kecamatan.update');
    Route::delete('/kecamatan/{kecamatan}', [ManajemenController::class, 'kecamatanDestroy'])->name('kecamatan.destroy');
    
    // Manajemen Desa
    Route::get('/desa', [ManajemenController::class, 'desaIndex'])->name('desa.index');
    Route::post('/desa', [ManajemenController::class, 'desaStore'])->name('desa.store');
    Route::get('/desa/{desa}/edit', [ManajemenController::class, 'desaEdit'])->name('desa.edit');
    Route::patch('/desa/{desa}', [ManajemenController::class, 'desaUpdate'])->name('desa.update');
    Route::delete('/desa/{desa}', [ManajemenController::class, 'desaDestroy'])->name('desa.destroy');

    // Manajemen Jenis Permohonan
    Route::get('/jenis-permohonan', [ManajemenController::class, 'permohonanIndex'])->name('permohonan.index');
    Route::post('/jenis-permohonan', [ManajemenController::class, 'permohonanStore'])->name('permohonan.store');
    Route::get('/jenis-permohonan/{jenisPermohonan}/edit', [ManajemenController::class, 'permohonanEdit'])->name('permohonan.edit');
    Route::patch('/jenis-permohonan/{jenisPermohonan}', [ManajemenController::class, 'permohonanUpdate'])->name('permohonan.update');
    Route::delete('/jenis-permohonan/{jenisPermohonan}', [ManajemenController::class, 'permohonanDestroy'])->name('permohonan.destroy');

    // Manajemen Petugas Ukur
    Route::get('/petugas-ukur', [ManajemenController::class, 'petugasUkurIndex'])->name('petugas-ukur.index');
    Route::get('/petugas-ukur/create', [ManajemenController::class, 'petugasUkurCreate'])->name('petugas-ukur.create');
    Route::post('/petugas-ukur', [ManajemenController::class, 'petugasUkurStore'])->name('petugas-ukur.store');
    Route::get('/petugas-ukur/{petugasUkur}/edit', [ManajemenController::class, 'petugasUkurEdit'])->name('petugas-ukur.edit');
    Route::patch('/petugas-ukur/{petugasUkur}', [ManajemenController::class, 'petugasUkurUpdate'])->name('petugas-ukur.update');
    Route::delete('/petugas-ukur/{petugasUkur}', [ManajemenController::class, 'petugasUkurDestroy'])->name('petugas-ukur.destroy');
    
    // Setting Area Kerja
    Route::get('/setting-area-kerja', [ManajemenController::class, 'settingAreaKerjaIndex'])->name('setting-area-kerja.index');
    Route::post('/setting-area-kerja', [ManajemenController::class, 'settingAreaKerjaUpdate'])->name('setting-area-kerja.update');

    // --- MANAJEMEN WA TEMPLATE (BARU) ---
    // Menggunakan Resource Controller agar otomatis membuat route: index, store, update, destroy
    Route::resource('wa-templates', WaTemplateController::class)->except(['create', 'edit', 'show']);
});

require __DIR__.'/auth.php';