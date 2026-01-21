<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use Illuminate\Http\Request;

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
        // 1. Validasi Input Form
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

    public function edit(WaTemplate $waTemplate)
    {
        $placeholders = WaPlaceholder::all();
        return view('admin.wa-templates.edit', compact('waTemplate', 'placeholders'));
    }

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
            // 'status' => $request->status ?? $waTemplate->status, // Jika ingin ubah status di edit
        ]);

        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WA berhasil diperbarui.');
    }

    public function destroy(WaTemplate $waTemplate)
    {
        $waTemplate->delete();
        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }
}