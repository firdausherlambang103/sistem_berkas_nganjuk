<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaPlaceholder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <--- WAJIB ADA AGAR TIDAK ERROR

class WaPlaceholderController extends Controller
{
    public function index()
    {
        // Ambil data dan urutkan
        $placeholders = WaPlaceholder::orderBy('code')->get();
        return view('admin.wa-placeholders.index', compact('placeholders'));
    }

    public function store(Request $request)
    {
        // 1. FORMAT KODE OTOMATIS (Cth: "nama" -> "{nama}")
        $code = trim($request->code);
        if ($code && !str_starts_with($code, '{')) $code = '{' . $code;
        if ($code && !str_ends_with($code, '}')) $code = $code . '}';

        // 2. MASUKKAN KEMBALI KE REQUEST UNTUK VALIDASI
        $request->merge(['code' => $code]);

        // 3. VALIDASI
        $request->validate([
            'code' => 'required|string|max:255|unique:wa_placeholders,code',
            'description' => 'required|string',
            'example' => 'nullable|string',
        ], [
            // Pesan Error Bahasa Indonesia (Opsional)
            'code.unique' => 'Kode placeholder ini sudah ada (duplikat).',
            'code.required' => 'Kode wajib diisi.',
        ]);

        // 4. SIMPAN KE DATABASE
        WaPlaceholder::create([
            'code' => $code,
            'description' => $request->description,
            'example' => $request->example
        ]);

        return back()->with('success', 'Placeholder berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        // Cari manual agar aman
        $wa_placeholder = WaPlaceholder::findOrFail($id);

        // 1. FORMAT KODE
        $code = trim($request->code);
        if ($code && !str_starts_with($code, '{')) $code = '{' . $code;
        if ($code && !str_ends_with($code, '}')) $code = $code . '}';

        $request->merge(['code' => $code]);

        // 2. VALIDASI (Kecualikan ID ini dari cek unique)
        $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('wa_placeholders', 'code')->ignore($wa_placeholder->id)],
            'description' => 'required|string',
            'example' => 'nullable|string',
        ]);

        // 3. UPDATE
        $wa_placeholder->update([
            'code' => $code,
            'description' => $request->description,
            'example' => $request->example
        ]);

        return back()->with('success', 'Placeholder berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $wa_placeholder = WaPlaceholder::findOrFail($id);
        $wa_placeholder->delete();
        return back()->with('success', 'Placeholder dihapus.');
    }
}