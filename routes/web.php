<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuangKerjaController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ManajemenController;
use App\Http\Controllers\Admin\WaTemplateController; // Pastikan di-import
use App\Http\Controllers\Admin\WaPlaceholderController; // Pastikan di-import
use App\Http\Controllers\Admin\WaLogController; // Pastikan di-import
use App\Http\Controllers\WhatsappWebController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\JadwalUkurController;
use App\Http\Controllers\SuratTugasController;
use App\Http\Controllers\SensusWakafController;
use App\Models\WaTemplate;
use App\Models\WaLog;
use App\Http\Controllers\PeminjamanBukuTanahController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/total', [DashboardController::class, 'showTotal'])->name('dashboard.total');
    Route::get('/dashboard/diproses', [DashboardController::class, 'showDiproses'])->name('dashboard.diproses');
    Route::get('/dashboard/selesai', [DashboardController::class, 'showSelesai'])->name('dashboard.selesai');
    Route::get('/dashboard/jatuh-tempo', [DashboardController::class, 'showJatuhTempo'])->name('dashboard.jatuh-tempo');
    Route::get('/dashboard/ditutup', [DashboardController::class, 'showDitutup'])->name('dashboard.ditutup');
    
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

    // --- RUANG KERJA ---
    Route::get('/ruang-kerja', [RuangKerjaController::class, 'index'])->name('ruang-kerja');
    Route::post('/berkas/simpan-kuasa-ajax', [BerkasController::class, 'storeKuasaAjax'])->name('berkas.store-kuasa-ajax');

    // 1. Route Ajax (Untuk Auto Fill)
    Route::get('/ajax/cek-berkas-bt', [PeminjamanBukuTanahController::class, 'cekBerkas'])->name('ajax.cek-berkas-bt');

    // 2. Route Riwayat (WAJIB DI ATAS RESOURCE)
    Route::get('peminjaman-bt/riwayat', [PeminjamanBukuTanahController::class, 'riwayat'])->name('peminjaman-bt.riwayat');

    // 3. Route Resource (Index, Create, Store, Edit, Update, Destroy)
    Route::resource('peminjaman-bt', PeminjamanBukuTanahController::class);
    Route::post('/peminjaman-bt/proses/{berkasId}', [PeminjamanBukuTanahController::class, 'prosesOtomatis'])->name('peminjaman-bt.proses-otomatis');
    
    // --- FITUR WHATSAPP (API & SEND) ---
    Route::get('/api/wa-templates/{berkas_id?}', function ($berkas_id = null) {
        $templates = WaTemplate::where('status', 'aktif')->get();
        if ($berkas_id) {
            $templates->map(function ($tpl) use ($berkas_id) {
                $tpl->usage_count = WaLog::where('berkas_id', $berkas_id)
                                         ->where('template_id', $tpl->id)
                                         ->where('status', 'Sukses')
                                         ->count();
                return $tpl;
            });
        }
        return response()->json($templates);
    })->name('api.wa-templates');

    // Route untuk mengirim pesan (AJAX / Umum)
    Route::post('/whatsapp/send', [WhatsappWebController::class, 'send'])->name('whatsapp.send');

    // --- BERKAS ---
    Route::prefix('berkas')->name('berkas.')->controller(BerkasController::class)->group(function() {
        Route::get('/baru', 'create')->name('create')->middleware('can:create-berkas');
        Route::post('/', 'store')->name('store')->middleware('can:create-berkas');
        Route::post('/kirim', 'kirim')->name('kirim');
        
        Route::get('/{berkas}/edit', 'edit')->name('edit'); 
        Route::put('/{berkas}', 'update')->name('update'); 
        Route::patch('/{berkas}', 'update'); 
        
        Route::post('/{berkas}/terima', 'terima')->name('terima');
        Route::post('/{berkas}/tolak', 'tolak')->name('tolak');
        Route::post('/{berkas}/selesaikan', 'selesaikan')->name('selesaikan');
        Route::post('/{berkas}/tutup', 'tutup')->name('tutup');
        Route::post('/{berkas}/pending', 'pending')->name('pending');
        Route::post('/{berkas}/aktifkan', 'aktifkan')->name('aktifkan');
        
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

     // --- SURAT TUGAS ---
    Route::prefix('surat-tugas')->name('surat-tugas.')->controller(SuratTugasController::class)->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/generate', 'generate')->name('generate');
    });
});

// --- ROUTE ADMIN ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // User & Master Data
    Route::get('/users-approval', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/penerima-kuasa', [ManajemenController::class, 'kuasaIndex'])->name('kuasa.index');
    Route::post('/penerima-kuasa', [ManajemenController::class, 'kuasaStore'])->name('kuasa.store');
    Route::patch('/penerima-kuasa/{kuasa}', [ManajemenController::class, 'kuasaUpdate'])->name('kuasa.update');
    Route::delete('/penerima-kuasa/{kuasa}', [ManajemenController::class, 'kuasaDestroy'])->name('kuasa.destroy');

    Route::get('/jabatan', [ManajemenController::class, 'jabatanIndex'])->name('jabatan.index');
    Route::post('/jabatan', [ManajemenController::class, 'jabatanStore'])->name('jabatan.store');
    Route::get('/jabatan/{jabatan}/edit', [ManajemenController::class, 'jabatanEdit'])->name('jabatan.edit');
    Route::patch('/jabatan/{jabatan}', [ManajemenController::class, 'jabatanUpdate'])->name('jabatan.update');
    Route::delete('/jabatan/{jabatan}', [ManajemenController::class, 'jabatanDestroy'])->name('jabatan.destroy');

    Route::get('/kecamatan', [ManajemenController::class, 'kecamatanIndex'])->name('kecamatan.index');
    Route::post('/kecamatan', [ManajemenController::class, 'kecamatanStore'])->name('kecamatan.store');
    Route::get('/kecamatan/{kecamatan}/edit', [ManajemenController::class, 'kecamatanEdit'])->name('kecamatan.edit');
    Route::patch('/kecamatan/{kecamatan}', [ManajemenController::class, 'kecamatanUpdate'])->name('kecamatan.update');
    Route::delete('/kecamatan/{kecamatan}', [ManajemenController::class, 'kecamatanDestroy'])->name('kecamatan.destroy');
    
    Route::get('/desa', [ManajemenController::class, 'desaIndex'])->name('desa.index');
    Route::post('/desa', [ManajemenController::class, 'desaStore'])->name('desa.store');
    Route::get('/desa/{desa}/edit', [ManajemenController::class, 'desaEdit'])->name('desa.edit');
    Route::patch('/desa/{desa}', [ManajemenController::class, 'desaUpdate'])->name('desa.update');
    Route::delete('/desa/{desa}', [ManajemenController::class, 'desaDestroy'])->name('desa.destroy');

    Route::get('/jenis-permohonan', [ManajemenController::class, 'permohonanIndex'])->name('permohonan.index');
    Route::post('/jenis-permohonan', [ManajemenController::class, 'permohonanStore'])->name('permohonan.store');
    Route::get('/jenis-permohonan/{jenisPermohonan}/edit', [ManajemenController::class, 'permohonanEdit'])->name('permohonan.edit');
    Route::patch('/jenis-permohonan/{jenisPermohonan}', [ManajemenController::class, 'permohonanUpdate'])->name('permohonan.update');
    Route::delete('/jenis-permohonan/{jenisPermohonan}', [ManajemenController::class, 'permohonanDestroy'])->name('permohonan.destroy');

    Route::get('/petugas-ukur', [ManajemenController::class, 'petugasUkurIndex'])->name('petugas-ukur.index');
    Route::get('/petugas-ukur/create', [ManajemenController::class, 'petugasUkurCreate'])->name('petugas-ukur.create');
    Route::post('/petugas-ukur', [ManajemenController::class, 'petugasUkurStore'])->name('petugas-ukur.store');
    Route::get('/petugas-ukur/{petugasUkur}/edit', [ManajemenController::class, 'petugasUkurEdit'])->name('petugas-ukur.edit');
    Route::patch('/petugas-ukur/{petugasUkur}', [ManajemenController::class, 'petugasUkurUpdate'])->name('petugas-ukur.update');
    Route::delete('/petugas-ukur/{petugasUkur}', [ManajemenController::class, 'petugasUkurDestroy'])->name('petugas-ukur.destroy');
    
    Route::get('/setting-area-kerja', [ManajemenController::class, 'settingAreaKerjaIndex'])->name('setting-area-kerja.index');
    Route::post('/setting-area-kerja', [ManajemenController::class, 'settingAreaKerjaUpdate'])->name('setting-area-kerja.update');

    
    // --- WHATSAPP MANAGEMENT (Perbaikan Routing) ---
    
    // 1. Log / Riwayat WA
    Route::get('/wa-logs', [WaLogController::class, 'index'])->name('wa-logs.index');

    // 2. Scan QR (Halaman & Aksi)
    Route::get('/whatsapp/scan', [WhatsappWebController::class, 'scan'])->name('whatsapp.scan');
    Route::post('/whatsapp/send-test', [WhatsappWebController::class, 'sendTest'])->name('whatsapp.send-test'); // Tambahan
    Route::post('/whatsapp/logout', [WhatsappWebController::class, 'logout'])->name('whatsapp.logout'); // Tambahan

    // 3. WA Templates & Placeholders (Resource Route)
    //Route::resource('wa-templates', WaTemplateController::class);
    Route::resource('wa-placeholders', WaPlaceholderController::class);
    // ...
    Route::resource('wa-templates', WaTemplateController::class);

});

require __DIR__.'/auth.php';