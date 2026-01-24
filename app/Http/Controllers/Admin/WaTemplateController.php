<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use App\Models\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WaTemplateController extends Controller
{
    /**
     * Menampilkan daftar template.
     */
    public function index()
    {
        $templates = WaTemplate::latest()->paginate(10);
        return view('admin.wa-templates.index', compact('templates'));
    }

    /**
     * Form tambah template baru.
     */
    public function create()
    {
        $placeholders = WaPlaceholder::all();
        return view('admin.wa-templates.create', compact('placeholders'));
    }

    /**
     * Simpan template baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Form (nama_template & isi_pesan)
        $request->validate([
            'nama_template' => 'required|unique:wa_templates,nama', // Cek unique di kolom 'nama'
            'isi_pesan' => 'required',
        ]);

        // 2. Simpan ke Database (Mapping Input -> Kolom DB)
        WaTemplate::create([
            'nama' => $request->nama_template,
            'template' => $request->isi_pesan,
            'status' => 'aktif', // Default status
        ]);

        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WA berhasil ditambahkan.');
    }

    /**
     * Form edit template.
     */
    public function edit(WaTemplate $waTemplate)
    {
        $placeholders = WaPlaceholder::all();
        return view('admin.wa-templates.edit', compact('waTemplate', 'placeholders'));
    }

    /**
     * Update template di database.
     */
    public function update(Request $request, WaTemplate $waTemplate)
    {
        // 1. Validasi
        $request->validate([
            'nama_template' => 'required|unique:wa_templates,nama,' . $waTemplate->id,
            'isi_pesan' => 'required',
        ]);

        // 2. Update Data
        $waTemplate->update([
            'nama' => $request->nama_template,
            'template' => $request->isi_pesan,
        ]);

        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WA berhasil diperbarui.');
    }

    /**
     * Hapus template.
     */
    public function destroy(WaTemplate $waTemplate)
    {
        $waTemplate->delete();
        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    // ========================================================================
    // API INTERNAL UNTUK MODAL WHATSAPP (RUANG KERJA)
    // ========================================================================

    /**
     * Mengambil daftar template aktif dalam format JSON.
     * Endpoint: /ajax/wa-templates
     */
    public function getJsonList()
    {
        $templates = WaTemplate::where('status', 'aktif')->latest()->get();
        return response()->json($templates);
    }

    /**
     * Melakukan preview pesan dengan mengganti placeholder secara real-time.
     * Endpoint: /ajax/wa-preview
     */
    public function previewMessage(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:wa_templates,id',
            'berkas_id'   => 'required|exists:berkas,id',
        ]);

        try {
            $template = WaTemplate::find($request->template_id);
            
            // Load berkas beserta relasi agar placeholder relasi (misal: desa.nama_desa) terbaca
            $berkas = Berkas::with([
                'dataDesa', 
                'dataKecamatan', 
                'jenisPermohonan', 
                'posisiSekarang',
                'penerimaKuasa'
            ])->find($request->berkas_id);

            if (!$template || !$berkas) {
                return response()->json(['message' => 'Data template atau berkas tidak ditemukan'], 404);
            }

            // Gunakan WaService untuk parsing (menggunakan Anonymous Class untuk akses method protected)
            // Ini trik aman agar kita menggunakan logika yang sama persis dengan pengiriman asli
            $waService = new class extends \App\Services\WaService {
                public function exposeParse($msg, $data) {
                    return $this->parseTemplate($msg, $data);
                }
            };

            $parsedMessage = $waService->exposeParse($template->template, $berkas);

            return response()->json(['message' => $parsedMessage]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memuat preview: ' . $e->getMessage()], 500);
        }
    }
}