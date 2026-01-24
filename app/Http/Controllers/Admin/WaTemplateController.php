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
            'nama_template' => 'required|unique:wa_templates,nama', // Pastikan cek unique di kolom 'nama'
            'isi_pesan' => 'required',
        ]);

        // 2. Simpan ke Database
        WaTemplate::create([
            'nama' => $request->nama_template,
            'template' => $request->isi_pesan, // Mapping input 'isi_pesan' ke kolom 'template'
            'status' => 'aktif',
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