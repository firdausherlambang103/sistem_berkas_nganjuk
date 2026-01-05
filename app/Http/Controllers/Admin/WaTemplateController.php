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
        // Ambil semua template
        $templates = WaTemplate::all();
        // Return ke view yang akan kita buat
        return view('admin.wa-templates.index', compact('templates'));
    }

    /**
     * Menyimpan template baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
        ]);

        WaTemplate::create([
            'judul' => $request->judul,
            'pesan' => $request->pesan,
            'is_active' => true
        ]);

        return redirect()->route('admin.wa-templates.index')->with('success', 'Template berhasil ditambahkan');
    }

    /**
     * Mengupdate template.
     */
    public function update(Request $request, WaTemplate $waTemplate)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
        ]);

        $waTemplate->update($request->only('judul', 'pesan'));

        return redirect()->route('admin.wa-templates.index')->with('success', 'Template berhasil diperbarui');
    }

    /**
     * Menghapus template.
     */
    public function destroy(WaTemplate $waTemplate)
    {
        $waTemplate->delete();
        return redirect()->route('admin.wa-templates.index')->with('success', 'Template berhasil dihapus');
    }
}