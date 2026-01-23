<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaPlaceholder;
use App\Models\Berkas; // Import Model Berkas untuk pengecekan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; // Untuk cek kolom database
use Illuminate\Support\Str; // Untuk manipulasi string

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

        // [BARU] Cek validitas deskripsi sebelum simpan
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
     * Mengecek apakah field/relasi yang diinput user benar-benar ada di Model Berkas.
     */
    protected function checkValidDeskripsi($path)
    {
        $berkas = new Berkas();
        
        // Pisahkan path jika ada titik (contoh: dataDesa.nama_desa)
        $parts = explode('.', $path);
        $first = $parts[0]; // Bagian pertama (relasi atau kolom)

        // 1. Handle Alias (Sesuai logika WaService agar tidak dianggap error)
        // Karena di WaService Anda mungkin melakukan replace manual
        if ($first === 'desa') $first = 'dataDesa'; // Alias ke relasi dataDesa
        if ($first === 'kecamatan') $first = 'dataKecamatan'; // Alias ke relasi dataKecamatan

        // 2. Cek Relasi (Jika input mengandung titik, misal: desa.nama_desa)
        if (count($parts) > 1) {
            // Cek apakah method relasi ada di model Berkas (contoh: public function dataDesa())
            if (!method_exists($berkas, $first)) {
                return "Relasi '$first' tidak ditemukan di Model Berkas. Pastikan nama relasi benar (contoh: gunakan 'dataDesa' bukan 'desa' jika tidak ada alias).";
            }
            return null; // Lolos validasi relasi (kita asumsikan properti child-nya benar)
        }

        // 3. Cek Kolom Biasa (Jika input tidak ada titik, misal: nama_pemohon)
        // Cek apakah ada di $fillable, kolom database, atau Accessor (get...Attribute)
        $isFillable = in_array($first, $berkas->getFillable());
        $isColumn = Schema::hasColumn($berkas->getTable(), $first);
        $isAccessor = method_exists($berkas, 'get'.Str::studly($first).'Attribute');

        if ($isFillable || $isColumn || $isAccessor) {
            return null; // Lolos validasi
        }

        return "Kolom atau atribut '$first' tidak ditemukan di tabel Berkas atau Model Berkas.";
    }
}