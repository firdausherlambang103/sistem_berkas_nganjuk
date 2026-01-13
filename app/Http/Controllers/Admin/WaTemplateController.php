<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaTemplate;
use Illuminate\Http\Request;

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
     * Menampilkan form buat template baru.
     */
    public function create()
    {
        return view('admin.wa-templates.create');
    }

    /**
     * Menyimpan template baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'template' => 'required|string',
            'status' => 'required|in:aktif,tidak_aktif',
        ]);

        WaTemplate::create($request->all());

        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WhatsApp berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit template.
     */
    public function edit($id)
    {
        $template = WaTemplate::findOrFail($id);
        return view('admin.wa-templates.edit', compact('template'));
    }

    /**
     * Memperbarui template.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'template' => 'required|string',
            'status' => 'required|in:aktif,tidak_aktif',
        ]);

        $template = WaTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WhatsApp berhasil diperbarui.');
    }

    /**
     * Menghapus template.
     */
    public function destroy($id)
    {
        $template = WaTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.wa-templates.index')
            ->with('success', 'Template WhatsApp berhasil dihapus.');
    }
}