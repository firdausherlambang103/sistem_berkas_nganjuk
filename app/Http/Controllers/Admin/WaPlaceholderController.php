<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaPlaceholder;
use Illuminate\Http\Request;

class WaPlaceholderController extends Controller
{
    public function index()
    {
        $placeholders = WaPlaceholder::orderBy('code')->get();
        return view('admin.wa-placeholders.index', compact('placeholders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:wa_placeholders,code',
            'description' => 'required',
        ]);

        // Pastikan format code pakai kurung kurawal
        $code = $request->code;
        if (!str_starts_with($code, '{')) $code = '{' . $code;
        if (!str_ends_with($code, '}')) $code = $code . '}';

        WaPlaceholder::create([
            'code' => $code,
            'description' => $request->description,
            'example' => $request->example
        ]);

        return back()->with('success', 'Placeholder berhasil ditambahkan.');
    }

    public function update(Request $request, WaPlaceholder $wa_placeholder)
    {
        $request->validate([
            'code' => 'required|unique:wa_placeholders,code,' . $wa_placeholder->id,
            'description' => 'required',
        ]);

        $code = $request->code;
        if (!str_starts_with($code, '{')) $code = '{' . $code;
        if (!str_ends_with($code, '}')) $code = $code . '}';

        $wa_placeholder->update([
            'code' => $code,
            'description' => $request->description,
            'example' => $request->example
        ]);

        return back()->with('success', 'Placeholder berhasil diperbarui.');
    }

    public function destroy(WaPlaceholder $wa_placeholder)
    {
        $wa_placeholder->delete();
        return back()->with('success', 'Placeholder dihapus.');
    }
}