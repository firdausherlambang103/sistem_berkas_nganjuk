<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaPlaceholder;
use Illuminate\Http\Request;

class WaPlaceholderController extends Controller
{
    /**
     * Menampilkan daftar placeholder.
     */
    public function index()
    {
        $placeholders = WaPlaceholder::latest()->paginate(10);
        return view('admin.wa-placeholders.index', compact('placeholders'));
    }

    /**
     * Menampilkan form buat placeholder baru.
     */
    public function create()
    {
        return view('admin.wa-placeholders.create');
    }

    /**
     * Menyimpan placeholder baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'placeholder' => 'required|string|unique:wa_placeholders,placeholder',
            'deskripsi' => 'nullable|string',
        ]);

        WaPlaceholder::create($request->all());

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit placeholder.
     */
    public function edit($id)
    {
        $placeholder = WaPlaceholder::findOrFail($id);
        return view('admin.wa-placeholders.edit', compact('placeholder'));
    }

    /**
     * Memperbarui placeholder.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'placeholder' => 'required|string|unique:wa_placeholders,placeholder,' . $id,
            'deskripsi' => 'nullable|string',
        ]);

        $placeholder = WaPlaceholder::findOrFail($id);
        $placeholder->update($request->all());

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil diperbarui.');
    }

    /**
     * Menghapus placeholder.
     */
    public function destroy($id)
    {
        $placeholder = WaPlaceholder::findOrFail($id);
        $placeholder->delete();

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil dihapus.');
    }
}