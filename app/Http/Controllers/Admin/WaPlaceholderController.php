<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaPlaceholder;
use App\Models\Berkas; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Str; 

class WaPlaceholderController extends Controller
{
    public function index()
    {
        $placeholders = WaPlaceholder::latest()->paginate(10);
        return view('admin.wa-placeholders.index', compact('placeholders'));
    }

    public function create()
    {
        return view('admin.wa-placeholders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'placeholder' => 'required|unique:wa_placeholders,placeholder',
            'deskripsi' => 'required', 
        ]);

        // [BARU] Cek validitas deskripsi dengan logika yang lebih longgar (support fallback)
        $errorMsg = $this->checkValidDeskripsi($request->deskripsi);
        if ($errorMsg) {
            return back()->withInput()->withErrors(['deskripsi' => $errorMsg]);
        }

        WaPlaceholder::create($request->all());

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil ditambahkan.');
    }

    public function edit(WaPlaceholder $waPlaceholder)
    {
        return view('admin.wa-placeholders.edit', compact('waPlaceholder'));
    }

    public function update(Request $request, WaPlaceholder $waPlaceholder)
    {
        $request->validate([
            'placeholder' => 'required|unique:wa_placeholders,placeholder,' . $waPlaceholder->id,
            'deskripsi' => 'required',
        ]);

        // [BARU] Cek validitas deskripsi sebelum update
        $errorMsg = $this->checkValidDeskripsi($request->deskripsi);
        if ($errorMsg) {
            return back()->withInput()->withErrors(['deskripsi' => $errorMsg]);
        }

        $waPlaceholder->update($request->all());

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil diperbarui.');
    }

    public function destroy(WaPlaceholder $waPlaceholder)
    {
        $waPlaceholder->delete();
        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil dihapus.');
    }

    /**
     * Fungsi Validasi Kustom
     * Mengecek apakah field/relasi yang diinput user valid di Model Berkas.
     * Diperbarui agar mendukung kolom string biasa (fallback system).
     */
    protected function checkValidDeskripsi($path)
    {
        $berkas = new Berkas();
        
        // Pisahkan path jika ada titik (contoh: dataDesa.nama_desa)
        $parts = explode('.', $path);
        $first = $parts[0]; // Bagian pertama (relasi atau kolom)

        // 1. Handle Alias (Sesuai logika WaService agar tidak dianggap error)
        if ($first === 'desa') $first = 'dataDesa'; 
        if ($first === 'kecamatan') $first = 'dataKecamatan'; 

        // 2. Cek Kolom Database Biasa (Prioritas Tinggi untuk Fallback)
        // Jika user memasukkan 'desa' (string) atau 'nama_pemohon', validasi ini akan meloloskannya.
        if (Schema::hasColumn($berkas->getTable(), $parts[0])) {
            return null; // Valid, ini kolom database
        }

        // 3. Cek Relasi (Jika input mengandung titik, misal: desa.nama_desa)
        if (count($parts) > 1) {
            // Cek apakah method relasi ada di model Berkas (contoh: public function dataDesa())
            if (method_exists($berkas, $first)) {
                return null; // Relasi ditemukan
            }
            
            // [TOLERANSI] Jika relasi tidak ketemu (misal user tulis 'desa.nama'), 
            // tapi kolom 'desa' ada di database sebagai string, kita loloskan.
            // Karena WaService punya logika fallback: jika relasi null, ambil string kolom 'desa'.
            if (($parts[0] == 'desa' || $parts[0] == 'kecamatan') && Schema::hasColumn($berkas->getTable(), $parts[0])) {
                return null;
            }

            return "Relasi '$first' tidak ditemukan di Model Berkas. Pastikan nama relasi benar (contoh: gunakan 'dataDesa' bukan 'desa' jika tidak ada alias).";
        }

        // 4. Cek Accessor atau Fillable (jika bukan kolom DB murni)
        $isFillable = in_array($first, $berkas->getFillable());
        $isAccessor = method_exists($berkas, 'get'.Str::studly($first).'Attribute');

        if ($isFillable || $isAccessor) {
            return null; // Lolos validasi
        }

        return "Kolom atau atribut '$first' tidak ditemukan di tabel Berkas atau Model Berkas.";
    }
}