<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berkas;
use App\Models\Tim;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratTugasController extends Controller
{
    /**
     * Menampilkan form untuk membuat surat tugas dan BA.
     */
    public function create()
    {
        $berkasAktif = Berkas::where('status', 'Diproses')->orderBy('nomer_berkas')->get();
        $tims = Tim::orderBy('nama_tim')->get();
        return view('surat_tugas.create', compact('berkasAktif', 'tims'));
    }

    /**
     * Mengambil detail berkas untuk auto-fill form via API.
     */
    public function getBerkasDetail($id)
    {
        $berkas = Berkas::find($id);
        if (!$berkas) {
            return response()->json(['error' => 'Berkas tidak ditemukan'], 404);
        }
        return response()->json($berkas); // Mengembalikan semua data berkas sebagai JSON
    }

    /**
     * Memproses data dari form dan menghasilkan dokumen Word.
     */
    public function generate(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            // Data Pemohon (opsional jika diisi manual)
            'berkas_id' => 'nullable|exists:berkas,id',
            'nama_pemohon' => 'required_without:berkas_id|string|max:255',
            'desa' => 'required_without:berkas_id|string|max:255',
            'kecamatan' => 'required_without:berkas_id|string|max:255',
            
            // Data Surat & BA
            'no_st' => 'required|string',
            'tgl_mulai_pa' => 'required|date',
            'selesai_pa' => 'required|date',
            'di_305' => 'required|string',
            'thn_di_305' => 'required|string',
            'tgl_peta_bidang' => 'required|date',
            'nib' => 'required|string',
            'no_leter_c' => 'required|string',
            'persil' => 'required|string',
            'klas' => 'required|string',
            'nama_letter_c' => 'required|string',
            'sporadik' => 'required|date',
            'tgl_skrt' => 'required|date',
            'no_skrt' => 'required|string',
            'bak_tanggal' => 'required|date',
            'bak_nomor' => 'required|string',
            'utara' => 'required|string',
            'timur' => 'required|string',
            'selatan' => 'required|string',
            'barat' => 'required|string',

            // Pilihan Dokumen & Tim
            'template_type' => 'required|in:st,ba',
            'tim_sidang_id' => 'nullable|exists:tims,id'
        ]);

        // 2. Menentukan Sumber Data Pemohon
        $dataPemohon = [];
        if ($request->filled('berkas_id')) {
            $berkas = Berkas::findOrFail($request->berkas_id);
            $dataPemohon['nama_pemohon'] = $berkas->nama_pemohon;
            $dataPemohon['desa'] = $berkas->desa;
            $dataPemohon['kecamatan'] = $berkas->kecamatan;
            $fileNameSuffix = str_replace('/', '_', $berkas->nomer_berkas); // Ganti slash agar nama file valid
        } else {
            $dataPemohon['nama_pemohon'] = $request->nama_pemohon;
            $dataPemohon['desa'] = $request->desa;
            $dataPemohon['kecamatan'] = $request->kecamatan;
            $fileNameSuffix = 'manual_' . date('Ymd_His');
        }

        // 3. Menentukan Template dan Tim
        $tim = $request->filled('tim_sidang_id') ? Tim::with('users')->find($request->tim_sidang_id) : null;
        $templateName = $request->template_type == 'st' ? 'ST 1 .doc' : 'BA 1.doc';
        $templatePath = storage_path('app/templates/' . $templateName);

        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'File template tidak ditemukan. Pastikan file ada di storage/app/templates/')->withInput();
        }

        try {
            // 4. Memproses Template Dokumen
            $templateProcessor = new TemplateProcessor($templatePath);
            $fileName = ($request->template_type == 'st' ? 'SuratTugas_' : 'BeritaAcara_') . $fileNameSuffix . '.docx';
            Carbon::setLocale('id'); // Set lokal ke bahasa Indonesia untuk format tanggal

            // Mengisi data pemohon
            $templateProcessor->setValue('Nama_Pemohon', $dataPemohon['nama_pemohon']);
            $templateProcessor->setValue('Desa', $dataPemohon['desa']);
            $templateProcessor->setValue('Kecamatan', $dataPemohon['kecamatan']);

            // Mengisi semua data lain dari form
            $templateProcessor->setValue('NO_ST', $request->no_st);
            $templateProcessor->setValue('Tgl_Mulai_PA', Carbon::parse($request->tgl_mulai_pa)->translatedFormat('d F Y'));
            $templateProcessor->setValue('Selesai_PA', Carbon::parse($request->selesai_pa)->translatedFormat('d F Y'));
            $templateProcessor->setValue('HARI', Carbon::parse($request->tgl_mulai_pa)->translatedFormat('l'));
            $templateProcessor->setValue('DI_305', $request->di_305);
            $templateProcessor->setValue('Thn_DI_305', $request->thn_di_305);
            $templateProcessor->setValue('Tgl_Peta_bidang', Carbon::parse($request->tgl_peta_bidang)->translatedFormat('d F Y'));
            $templateProcessor->setValue('NIB', $request->nib);
            $templateProcessor->setValue('No_Leter_C', $request->no_leter_c);
            $templateProcessor->setValue('Persil', $request->persil);
            $templateProcessor->setValue('Klas', $request->klas);
            $templateProcessor->setValue('Nama_Letter_C', $request->nama_letter_c);
            $templateProcessor->setValue('Sporadik', Carbon::parse($request->sporadik)->translatedFormat('d F Y'));
            $templateProcessor->setValue('Tgl_SKRT', Carbon::parse($request->tgl_skrt)->translatedFormat('d F Y'));
            $templateProcessor->setValue('No_SKRT', $request->no_skrt);
            $templateProcessor->setValue('BAK_Tanggal', Carbon::parse($request->bak_tanggal)->translatedFormat('d F Y'));
            $templateProcessor->setValue('BAK_Nomor', $request->bak_nomor);
            $templateProcessor->setValue('Utara', $request->utara);
            $templateProcessor->setValue('Timur', $request->timur);
            $templateProcessor->setValue('Selatan', $request->selatan);
            $templateProcessor->setValue('Barat', $request->barat);

            // Placeholder lain yang mungkin ada di template BA 1.doc
            $templateProcessor->setValue('AKTA_', $request->akta_ ?? '');
            $templateProcessor->setValue('TGL_AKTA', $request->tgl_akta ? Carbon::parse($request->tgl_akta)->translatedFormat('d F Y') : '');
            $templateProcessor->setValue('NO_AKTA', $request->no_akta ?? '');
            $templateProcessor->setValue('PPAT', $request->ppat ?? '');
            $templateProcessor->setValue('AKTA_IKRAR_WAQAF', $request->akta_ikrar_waqaf ?? '');
            $templateProcessor->setValue('NO_WAQAF', $request->no_waqaf ?? '');
            $templateProcessor->setValue('SKW_tgl_', $request->skw_tgl ? Carbon::parse($request->skw_tgl)->translatedFormat('d F Y') : '');
            $templateProcessor->setValue('SEGEL_TGL', $request->segel_tgl ? Carbon::parse($request->segel_tgl)->translatedFormat('d F Y') : '');
            $templateProcessor->setValue('Luas', $request->luas ?? '...');

            // Mengisi data tim secara dinamis
            if ($tim) {
                // Untuk blok Petugas Sidang di BA dan ST
                $templateProcessor->cloneBlock('petugas_sidang_block', $tim->users->count(), true, true);
                foreach ($tim->users as $index => $user) {
                    $templateProcessor->setValue('no_sidang#' . ($index + 1), $index + 1);
                    $templateProcessor->setValue('nama_sidang#' . ($index + 1), $user->name);
                    $templateProcessor->setValue('nip_sidang#' . ($index + 1), $user->nip ?? 'N/A');
                }
                
                // Untuk blok Petugas Lapang di BA dan ST
                $templateProcessor->cloneBlock('petugas_lapang_block', $tim->users->count(), true, true);
                foreach ($tim->users as $index => $user) {
                    $templateProcessor->setValue('no_lapang#' . ($index + 1), $index + 1);
                    $templateProcessor->setValue('nama_lapang#' . ($index + 1), $user->name);
                    $templateProcessor->setValue('nip_lapang#' . ($index + 1), $user->nip ?? 'N/A');
                }
            } else {
                // Jika tidak ada tim dipilih, hapus blok agar tidak muncul placeholder
                $templateProcessor->deleteBlock('petugas_sidang_block');
                $templateProcessor->deleteBlock('petugas_lapang_block');
            }
            
            // 5. Simpan dan Download Dokumen
            $generatedFilePath = storage_path('app/public/' . $fileName);
            $templateProcessor->saveAs($generatedFilePath);

            return response()->download($generatedFilePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage())->withInput();
        }
    }
}
