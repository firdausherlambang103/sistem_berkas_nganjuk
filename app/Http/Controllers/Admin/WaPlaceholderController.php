<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaPlaceholder;
use Illuminate\Http\Request;

class WaPlaceholderController extends Controller
{
    public function index()
    {
        $placeholders = WaPlaceholder::latest()->paginate(10);
        return view('admin.wa-placeholders.index', compact('placeholders'));
    }

    public function create()
    {
        return view('admin.wa-placeholders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'placeholder' => 'required|unique:wa_placeholders,placeholder',
            'deskripsi' => 'required', // Ini adalah nama kolom di tabel berkas
        ]);

        WaPlaceholder::create($request->all());

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil ditambahkan.');
    }

    public function edit(WaPlaceholder $waPlaceholder)
    {
        return view('admin.wa-placeholders.edit', compact('waPlaceholder'));
    }

    public function update(Request $request, WaPlaceholder $waPlaceholder)
    {
        $request->validate([
            'placeholder' => 'required|unique:wa_placeholders,placeholder,' . $waPlaceholder->id,
            'deskripsi' => 'required',
        ]);

        $waPlaceholder->update($request->all());

        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil diperbarui.');
    }

    public function destroy(WaPlaceholder $waPlaceholder)
    {
        $waPlaceholder->delete();
        return redirect()->route('admin.wa-placeholders.index')
            ->with('success', 'Placeholder berhasil dihapus.');
    }
}