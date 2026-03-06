<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuangKerjaController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ManajemenController;
use App\Http\Controllers\Admin\WaTemplateController;
use App\Http\Controllers\Admin\WaPlaceholderController;
use App\Http\Controllers\Admin\WaLogController;
use App\Http\Controllers\Admin\PerbaikanBerkasController;
use App\Http\Controllers\WhatsappWebController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\JadwalUkurController;
use App\Http\Controllers\SuratTugasController;
use App\Http\Controllers\SensusWakafController;
use App\Http\Controllers\PeminjamanBukuTanahController;

// Import Controller Khusus Mitra
use App\Http\Controllers\Mitra\AuthController as MitraAuthController;
use App\Http\Controllers\Mitra\PageController as MitraPageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// =================================================================
//  ROUTE KHUSUS MITRA (PPAT & FREELANCE)
// =================================================================
Route::prefix('mitra')->name('mitra.')->group(function () {
    
    // Route Guest (Belum Login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [MitraAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [MitraAuthController::class, 'storeLogin']);
        
        Route::get('/register', [MitraAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [MitraAuthController::class, 'storeRegister']);
    });

    // Route Auth (Sudah Login)
    Route::middleware(['auth', 'verified'])->group(function () {
        // Dashboard Khusus Mitra
        Route::get('/dashboard', [MitraPageController::class, 'dashboard'])->name('dashboard');
        
        // Ruang Kerja Mitra (Diarahkan ke RuangKerjaController bawaan agar saling terintegrasi)
        Route::get('/ruang-kerja', [RuangKerjaController::class, 'index'])->name('ruang-kerja');
    });
});

// =================================================================
// GROUP ROUTE UTAMA INTERNAL (Hanya user login & verifikasi email)
// =================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- DASHBOARD & UTAMA ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/total', [DashboardController::class, 'showTotal'])->name('dashboard.total');
    Route::get('/dashboard/diproses', [DashboardController::class, 'showDiproses'])->name('dashboard.diproses');
    Route::get('/dashboard/selesai', [DashboardController::class, 'showSelesai'])->name('dashboard.selesai');
    Route::get('/dashboard/jatuh-tempo', [DashboardController::class, 'showJatuhTempo'])->name('dashboard.jatuh-tempo');
    Route::get('/dashboard/ditutup', [DashboardController::class, 'showDitutup'])->name('dashboard.ditutup');
    
    // Ruang Kerja Internal
    Route::get('/ruang-kerja', [RuangKerjaController::class, 'index'])->name('ruang-kerja');

    // --- SENSUS WAKAF (PETA) ---
    Route::get('/sensus-wakaf', [SensusWakafController::class, 'index'])->name('sensus-wakaf.index');
    Route::get('/api/sensus-wakaf-data', [SensusWakafController::class, 'getMapData'])->name('sensus-wakaf.data');

    // --- LAPORAN ---
    Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
        Route::get('/rinci', 'index')->name('index');
        Route::get('/monitor', 'monitor')->name('monitor');
        Route::get('/user/{user}', 'showBerkasByUser')->name('berkas_by_user');
    });
    
    // --- PROFIL ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- MANAJEMEN BERKAS (Semua User Login) ---
    Route::post('/berkas/simpan-kuasa-ajax', [BerkasController::class, 'storeKuasaAjax'])->name('berkas.store-kuasa-ajax');
    
    Route::prefix('berkas')->name('berkas.')->controller(BerkasController::class)->group(function() {
        Route::get('/baru', 'create')->name('create')->middleware('can:create-berkas');
        Route::post('/', 'store')->name('store')->middleware('can:create-berkas');
        Route::post('/kirim', 'kirim')->name('kirim'); // Bulk Action
        
        Route::get('/{berkas}/edit', 'edit')->name('edit'); 
        Route::put('/{berkas}', 'update')->name('update'); 
        Route::patch('/{berkas}', 'update'); 
        
        Route::post('/{berkas}/terima', 'terima')->name('terima');
        Route::post('/{berkas}/tolak', 'tolak')->name('tolak');
        Route::post('/{berkas}/selesaikan', 'selesaikan')->name('selesaikan');
        Route::post('/{berkas}/tutup', 'tutup')->name('tutup');
        Route::post('/{berkas}/pending', 'pending')->name('pending');
        Route::post('/{berkas}/aktifkan', 'aktifkan')->name('aktifkan');
        
        // [DITAMBAHKAN] Route untuk Update Status Khusus
        Route::patch('/{berkas}/update-status', 'updateStatusKhusus')->name('update-status');
        
        Route::delete('/{berkas}', 'destroy')->name('destroy')->middleware('can:manage-berkas');
        Route::get('/{berkas}', 'show')->name('show');
    });

    // --- PEMINJAMAN BUKU TANAH ---
    Route::get('/ajax/cek-berkas-bt', [PeminjamanBukuTanahController::class, 'cekBerkas'])->name('ajax.cek-berkas-bt');
    Route::get('peminjaman-bt/riwayat', [PeminjamanBukuTanahController::class, 'riwayat'])->name('peminjaman-bt.riwayat');
    Route::resource('peminjaman-bt', PeminjamanBukuTanahController::class);
    Route::post('/peminjaman-bt/proses/{berkasId}', [PeminjamanBukuTanahController::class, 'prosesOtomatis'])->name('peminjaman-bt.proses-otomatis');

    // --- PENJADWALAN UKUR ---
    Route::prefix('penjadwalan-ukur')->name('jadwal-ukur.')->controller(JadwalUkurController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/dashboard-petugas', 'dashboardPetugas')->name('dashboard-petugas');
        Route::get('/pilih-petugas/{berkas?}', 'pilihPetugas')->name('pilih-petugas');
        Route::get('/input-jadwal/{petugasUkur}/{berkas?}', 'inputJadwal')->name('input-jadwal');
        Route::post('/simpan-jadwal', 'simpanJadwal')->name('simpan-jadwal');
    });

    // --- SURAT TUGAS ---
    Route::prefix('surat-tugas')->name('surat-tugas.')->controller(SuratTugasController::class)->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/generate', 'generate')->name('generate');
    });

    // --- WHATSAPP INTEGRATION (Umum / User Biasa) ---
    // API untuk Modal di Ruang Kerja
    Route::get('/ajax/wa-templates', [WaTemplateController::class, 'getJsonList'])->name('ajax.wa-templates');
    Route::post('/ajax/wa-preview', [WaTemplateController::class, 'previewMessage'])->name('ajax.wa-preview');
    // Action Mengirim Pesan
    Route::post('/whatsapp/send', [WhatsappWebController::class, 'sendMessage'])->name('whatsapp.send');
});

// =================================================================
//  ROUTE KHUSUS ADMIN
// =================================================================
// Middleware 'admin' wajib terdaftar di Kernel.php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // User & Approval
    Route::get('/users-approval', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::resource('users', AdminUserController::class)->except(['index', 'show']);

    // Manajemen Data Master
    Route::controller(ManajemenController::class)->group(function() {
        // Penerima Kuasa
        Route::get('/penerima-kuasa', 'kuasaIndex')->name('kuasa.index');
        Route::post('/penerima-kuasa', 'kuasaStore')->name('kuasa.store');
        Route::patch('/penerima-kuasa/{kuasa}', 'kuasaUpdate')->name('kuasa.update');
        Route::delete('/penerima-kuasa/{kuasa}', 'kuasaDestroy')->name('kuasa.destroy');

        // Jabatan
        Route::get('/jabatan', 'jabatanIndex')->name('jabatan.index');
        Route::post('/jabatan', 'jabatanStore')->name('jabatan.store');
        Route::get('/jabatan/{jabatan}/edit', 'jabatanEdit')->name('jabatan.edit');
        Route::patch('/jabatan/{jabatan}', 'jabatanUpdate')->name('jabatan.update');
        Route::delete('/jabatan/{jabatan}', 'jabatanDestroy')->name('jabatan.destroy');

        // Kecamatan
        Route::get('/kecamatan', 'kecamatanIndex')->name('kecamatan.index');
        Route::post('/kecamatan', 'kecamatanStore')->name('kecamatan.store');
        Route::get('/kecamatan/{kecamatan}/edit', 'kecamatanEdit')->name('kecamatan.edit');
        Route::patch('/kecamatan/{kecamatan}', 'kecamatanUpdate')->name('kecamatan.update');
        Route::delete('/kecamatan/{kecamatan}', 'kecamatanDestroy')->name('kecamatan.destroy');
        
        // Desa
        Route::get('/desa', 'desaIndex')->name('desa.index');
        Route::post('/desa', 'desaStore')->name('desa.store');
        Route::get('/desa/{desa}/edit', 'desaEdit')->name('desa.edit');
        Route::patch('/desa/{desa}', 'desaUpdate')->name('desa.update');
        Route::delete('/desa/{desa}', 'desaDestroy')->name('desa.destroy');

        // Jenis Permohonan
        Route::get('/jenis-permohonan', 'permohonanIndex')->name('permohonan.index');
        Route::post('/jenis-permohonan', 'permohonanStore')->name('permohonan.store');
        Route::get('/jenis-permohonan/{jenisPermohonan}/edit', 'permohonanEdit')->name('permohonan.edit');
        Route::patch('/jenis-permohonan/{jenisPermohonan}', 'permohonanUpdate')->name('permohonan.update');
        Route::delete('/jenis-permohonan/{jenisPermohonan}', 'permohonanDestroy')->name('permohonan.destroy');

        // Petugas Ukur
        Route::get('/petugas-ukur', 'petugasUkurIndex')->name('petugas-ukur.index');
        Route::get('/petugas-ukur/create', 'petugasUkurCreate')->name('petugas-ukur.create');
        Route::post('/petugas-ukur', 'petugasUkurStore')->name('petugas-ukur.store');
        Route::get('/petugas-ukur/{petugasUkur}/edit', 'petugasUkurEdit')->name('petugas-ukur.edit');
        Route::patch('/petugas-ukur/{petugasUkur}', 'petugasUkurUpdate')->name('petugas-ukur.update');
        Route::delete('/petugas-ukur/{petugasUkur}', 'petugasUkurDestroy')->name('petugas-ukur.destroy');
        
        // Master Status
        Route::get('/status', 'statusIndex')->name('status.index');
        Route::post('/status', 'statusStore')->name('status.store');
        Route::get('/status/{status}/edit', 'statusEdit')->name('status.edit');
        Route::patch('/status/{status}', 'statusUpdate')->name('status.update');
        Route::delete('/status/{status}', 'statusDestroy')->name('status.destroy');

        // Setting Area Kerja
        Route::get('/setting-area-kerja', 'settingAreaKerjaIndex')->name('setting-area-kerja.index');
        Route::post('/setting-area-kerja', 'settingAreaKerjaUpdate')->name('setting-area-kerja.update');
    });

    // --- WHATSAPP MANAGEMENT (Admin Panel) ---
    Route::get('/wa-logs', [WaLogController::class, 'index'])->name('wa-logs.index');
    
    // Halaman Scan Utama
    Route::get('/whatsapp/scan', [WhatsappWebController::class, 'scan'])->name('whatsapp.scan');
    
    // API Internal untuk Scan Page (AJAX)
    Route::get('/whatsapp/check-status', [WhatsappWebController::class, 'checkStatus'])->name('whatsapp.check-status');
    Route::get('/whatsapp/get-qr', [WhatsappWebController::class, 'getQr'])->name('whatsapp.get-qr');
    
    // Actions
    Route::post('/whatsapp/logout', [WhatsappWebController::class, 'logout'])->name('whatsapp.logout');
    Route::post('/whatsapp/send-test', [WhatsappWebController::class, 'sendTest'])->name('whatsapp.send-test');

    Route::resource('wa-templates', WaTemplateController::class);
    Route::resource('wa-placeholders', WaPlaceholderController::class);

    // --- PERBAIKAN BERKAS (Tool Admin) ---
    Route::get('/perbaikan-berkas', [PerbaikanBerkasController::class, 'index'])->name('perbaikan.index');
    Route::patch('/perbaikan-berkas/{id}', [PerbaikanBerkasController::class, 'update'])->name('perbaikan.update');

});

require __DIR__.'/auth.php';