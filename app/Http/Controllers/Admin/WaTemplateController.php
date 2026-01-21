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
        $request->validate([
            'nama_template' => 'required|unique:wa_templates,nama_template',
            'isi_pesan' => 'required',
        ]);

        WaTemplate::create($request->all());

        // PERBAIKAN DI SINI: Tambahkan 'admin.'
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
        $request->validate([
            'nama_template' => 'required|unique:wa_templates,nama_template,' . $waTemplate->id,
            'isi_pesan' => 'required',
        ]);

        $waTemplate->update($request->all());

        // PERBAIKAN DI SINI: Tambahkan 'admin.'
        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WA berhasil diperbarui.');
    }

    public function destroy(WaTemplate $waTemplate)
    {
        $waTemplate->delete();
        
        // PERBAIKAN DI SINI: Tambahkan 'admin.'
        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }
}