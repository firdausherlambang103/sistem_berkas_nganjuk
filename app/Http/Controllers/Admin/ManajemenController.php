<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PetugasUkur;
use App\Models\Kecamatan;
use App\Models\Jabatan;
use App\Models\Desa;
use App\Models\JenisPermohonan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;


class ManajemenController extends Controller
{
    // --- MANAJEMEN JABATAN ---

    public function jabatanIndex()
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        return view('admin.manajemen.jabatan', compact('jabatans'));
    }

    public function jabatanStore(Request $request)
    {
        $request->validate(['nama_jabatan' => 'required|string|unique:jabatans,nama_jabatan', 'is_admin' => 'nullable|boolean']);
        Jabatan::create(['nama_jabatan' => $request->nama_jabatan, 'is_admin' => $request->has('is_admin')]);
        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan baru berhasil ditambahkan.');
    }

    public function jabatanEdit(Jabatan $jabatan)
    {
        return view('admin.manajemen.jabatan-edit', compact('jabatan'));
    }

    public function jabatanUpdate(Request $request, Jabatan $jabatan)
    {
        $request->validate(['nama_jabatan' => 'required|string|unique:jabatans,nama_jabatan,' . $jabatan->id, 'is_admin' => 'nullable|boolean']);
        $jabatan->nama_jabatan = $request->nama_jabatan;
        $jabatan->is_admin = $request->has('is_admin');
        $jabatan->save();
        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function jabatanDestroy(Jabatan $jabatan)
    {
        if ($jabatan->users()->count() > 0) {
            return redirect()->route('admin.jabatan.index')->with('error', 'Jabatan tidak bisa dihapus karena masih digunakan oleh user.');
        }
        $jabatan->delete();
        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil dihapus.');
    }

    // --- MANAJEMEN KECAMATAN ---

    public function kecamatanIndex()
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        return view('admin.manajemen.kecamatan', compact('kecamatans'));
    }

    public function kecamatanStore(Request $request)
    {
        $request->validate(['nama_kecamatan' => 'required|string|max:255|unique:kecamatans,nama_kecamatan']);
        Kecamatan::create($request->all());
        return redirect()->route('admin.kecamatan.index')->with('success', 'Kecamatan baru berhasil ditambahkan.');
    }

    public function kecamatanEdit(Kecamatan $kecamatan)
    {
        return view('admin.manajemen.kecamatan-edit', compact('kecamatan'));
    }

    public function kecamatanUpdate(Request $request, Kecamatan $kecamatan)
    {
        $request->validate(['nama_kecamatan' => 'required|string|max:255|unique:kecamatans,nama_kecamatan,' . $kecamatan->id]);
        $kecamatan->update($request->all());
        return redirect()->route('admin.kecamatan.index')->with('success', 'Kecamatan berhasil diperbarui.');
    }

    public function kecamatanDestroy(Kecamatan $kecamatan)
    {
        if ($kecamatan->desas()->count() > 0) {
            return redirect()->route('admin.kecamatan.index')->with('error', 'Kecamatan tidak bisa dihapus karena memiliki desa terkait.');
        }
        $kecamatan->delete();
        return redirect()->route('admin.kecamatan.index')->with('success', 'Kecamatan berhasil dihapus.');
    }

    // --- MANAJEMEN DESA ---

    public function desaIndex()
    {
        $desas = Desa::with('kecamatan')->orderBy('nama_desa')->get();
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        return view('admin.manajemen.desa', compact('desas', 'kecamatans'));
    }

    public function desaStore(Request $request)
    {
        $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id', 'nama_desa' => 'required|string|max:255']);
        Desa::create($request->all());
        return redirect()->route('admin.desa.index')->with('success', 'Desa baru berhasil ditambahkan.');
    }

    public function desaEdit(Desa $desa)
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        return view('admin.manajemen.desa-edit', compact('desa', 'kecamatans'));
    }

    public function desaUpdate(Request $request, Desa $desa)
    {
        $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id', 'nama_desa' => 'required|string|max:255']);
        $desa->update($request->all());
        return redirect()->route('admin.desa.index')->with('success', 'Desa berhasil diperbarui.');
    }

    public function desaDestroy(Desa $desa)
    {
        $desa->delete();
        return redirect()->route('admin.desa.index')->with('success', 'Desa berhasil dihapus.');
    }

    // --- MANAJEMEN JENIS PERMOHONAN ---

    public function permohonanIndex()
    {
        $permohonans = JenisPermohonan::orderBy('nama_permohonan')->get();
        return view('admin.manajemen.permohonan', compact('permohonans'));
    }

    public function permohonanStore(Request $request)
    {
        $request->validate(['nama_permohonan' => 'required|string|unique:jenis_permohonans,nama_permohonan', 'waktu_timeline_hari' => 'required|integer|min:1']);
        JenisPermohonan::create($request->all());
        return redirect()->route('admin.permohonan.index')->with('success', 'Jenis Permohonan baru berhasil ditambahkan.');
    }
    
    public function permohonanEdit(JenisPermohonan $jenisPermohonan)
    {
        return view('admin.manajemen.permohonan-edit', compact('jenisPermohonan'));
    }

    public function permohonanUpdate(Request $request, JenisPermohonan $jenisPermohonan)
    {
        $request->validate(['nama_permohonan' => 'required|string|unique:jenis_permohonans,nama_permohonan,' . $jenisPermohonan->id, 'waktu_timeline_hari' => 'required|integer|min:1']);
        $jenisPermohonan->update($request->all());
        return redirect()->route('admin.permohonan.index')->with('success', 'Jenis Permohonan berhasil diperbarui.');
    }

    public function permohonanDestroy(JenisPermohonan $jenisPermohonan)
    {
        if ($jenisPermohonan->berkas()->count() > 0) {
            return redirect()->route('admin.permohonan.index')->with('error', 'Jenis Permohonan tidak bisa dihapus karena masih digunakan oleh berkas.');
        }
        $jenisPermohonan->delete();
        return redirect()->route('admin.permohonan.index')->with('success', 'Jenis Permohonan berhasil dihapus.');
    }
    
    //==============================================
    // MANAJEMEN PETUGAS UKUR
    //==============================================
    public function petugasUkurIndex(): View
    {
        $semuaPetugas = PetugasUkur::with('user.jabatan', 'areaKerja')->get();
        return view('admin.petugas-ukur.index', compact('semuaPetugas'));
    }

    public function petugasUkurCreate(): View
    {
        $users = User::whereDoesntHave('petugasUkur')
                     ->whereHas('jabatan', function ($query) {
                         $query->where('nama_jabatan', 'like', '%Petugas Ukur%');
                     })
                     ->orderBy('name')
                     ->get();
                     
        return view('admin.petugas-ukur.create', compact('users'));
    }

    public function petugasUkurStore(Request $request): RedirectResponse
    {
        // Validasi diubah: 'area_kerja' tidak lagi divalidasi di sini
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:petugas_ukur,user_id',
            'keahlian' => 'required|string|max:255',
        ]);
        
        // HANYA simpan data yang ada di tabel petugas_ukur
        PetugasUkur::create([
            'user_id' => $request->user_id,
            'keahlian' => $request->keahlian,
        ]);

        return redirect()->route('admin.petugas-ukur.index')->with('success', 'Petugas ukur baru berhasil ditambahkan.');
    }

    public function petugasUkurEdit(PetugasUkur $petugasUkur): View
    {
        return view('admin.petugas-ukur.edit', compact('petugasUkur'));
    }

    public function petugasUkurUpdate(Request $request, PetugasUkur $petugasUkur): RedirectResponse
    {
        // Validasi diubah: 'area_kerja' tidak lagi divalidasi di sini
        $request->validate([
            'keahlian' => 'required|string|max:255',
        ]);
        
        // HANYA update data yang ada di tabel petugas_ukur
        $petugasUkur->update([
            'keahlian' => $request->keahlian,
        ]);

        return redirect()->route('admin.petugas-ukur.index')->with('success', 'Data petugas ukur berhasil diperbarui.');
    }

    public function petugasUkurDestroy(PetugasUkur $petugasUkur): RedirectResponse
    {
        $petugasUkur->delete();
        return redirect()->route('admin.petugas-ukur.index')->with('success', 'Petugas ukur berhasil dihapus.');
    }
    
    // ... (sisa method tidak berubah) ...
    public function settingAreaKerjaIndex(): View
    {
        $semuaPetugas = PetugasUkur::with('user.jabatan', 'areaKerja')->orderBy('id')->get();
        $semuaKecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('admin.setting-area-kerja', compact('semuaPetugas', 'semuaKecamatan'));
    }

    public function settingAreaKerjaUpdate(Request $request): RedirectResponse
    {
        $data = $request->input('area_kerja', []);

        foreach (PetugasUkur::all() as $petugas) {
            $kecamatanIds = $data[$petugas->id] ?? [];
            $petugas->areaKerja()->sync($kecamatanIds);
        }

        return redirect()->back()->with('success', 'Pengaturan area kerja berhasil diperbarui.');
    }
}

