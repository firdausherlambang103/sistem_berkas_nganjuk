<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use App\Models\Berkas;
use App\Services\WaService;
use Illuminate\Http\Request;
use Throwable;

class WaTemplateController extends Controller
{
    public function index()
    {
        $templates = WaTemplate::latest()->paginate(10);
        return view('admin.wa-templates.index', compact('templates'));
    }

    public function create()
    {
        $placeholders = WaPlaceholder::all();
        return view('admin.wa-templates.create', compact('placeholders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|unique:wa_templates,nama',
            'isi_pesan' => 'required',
        ]);

        WaTemplate::create([
            'nama' => $request->nama_template,
            'template' => $request->isi_pesan,
            'status' => 'aktif', 
        ]);

        return redirect()->route('admin.wa-templates.index')->with('success', 'Template WA berhasil ditambahkan.');
    }

    public function edit(WaTemplate $waTemplate)
    {
        $placeholders = WaPlaceholder::all();
        return view('admin.wa-templates.edit', compact('waTemplate', 'placeholders'));
    }

    public function update(Request $request, WaTemplate $waTemplate)
    {
        $request->validate([
            'nama_template' => 'required|unique:wa_templates,nama,' . $waTemplate->id,
            'isi_pesan' => 'required',
        ]);

        $waTemplate->update([
            'nama' => $request->nama_template,
            'template' => $request->isi_pesan,
        ]);

        return redirect()->route('admin.wa-templates.index')->with('success', 'Template WA berhasil diperbarui.');
    }

    public function destroy(WaTemplate $waTemplate)
    {
        $waTemplate->delete();
        return redirect()->route('admin.wa-templates.index')->with('success', 'Template berhasil dihapus.');
    }

    public function getJsonList()
    {
        $templates = WaTemplate::where('status', 'aktif')->latest()->get();
        return response()->json($templates);
    }

    public function previewMessage(Request $request)
    {
        try {
            $request->validate([
                'template_id' => 'required|exists:wa_templates,id',
                'berkas_id'   => 'required|exists:berkas,id',
            ]);

            $template = WaTemplate::find($request->template_id);
            $berkas = Berkas::with([
                'dataDesa', 
                'dataKecamatan', 
                'jenisPermohonan', 
                'posisiSekarang',
                'penerimaKuasa'
            ])->find($request->berkas_id);

            if (!$template || !$berkas) {
                // Return dengan status 200 agar UI bisa merender pesannya
                return response()->json(['message' => 'Teks gagal diproses: Data template atau berkas tidak ditemukan']);
            }

            $waService = new WaService();
            $msgTemplate = $template->template ?? ''; 
            
            $parsedData = $waService->parseMediaTemplate($msgTemplate, $berkas);
            $pesanPreview = $parsedData['message'];

            if (!empty($parsedData['media_urls'])) {
                $pesanPreview .= "\n\n*(Sistem akan mengirimkan dokumen PDF/Lampiran secara otomatis)*";
            }

            return response()->json(['message' => $pesanPreview]);

        } catch (Throwable $e) {
            // [PENTING] Jika PHP Error, kita tangkap dan kirim ke UI agar jelas
            $errorMessage = "⚠️ ERROR SISTEM:\n" . $e->getMessage() . "\n\nFile: " . basename($e->getFile()) . " Baris: " . $e->getLine();
            \Illuminate\Support\Facades\Log::error('WA Preview Error: ' . $e->getMessage() . " pada baris " . $e->getLine());
            
            // Kita ubah menjadi response()->json normal (status 200) agar JavaScript bisa memunculkan teks merahnya di dalam kotak pratinjau.
            return response()->json(['message' => $errorMessage]);
        }
    }
}