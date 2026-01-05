<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaLog;
use Illuminate\Http\Request;

class WaLogController extends Controller
{
    /**
     * Menampilkan daftar riwayat pengiriman WA.
     */
    public function index()
    {
        // Ambil data log, urutkan dari yang terbaru
        $logs = WaLog::with(['user', 'berkas'])
                     ->latest()
                     ->paginate(20);

        return view('admin.wa-logs.index', compact('logs'));
    }
}