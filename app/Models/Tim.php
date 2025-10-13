<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\Request;

class TimController extends Controller
{
    public function index()
    {
        $tims = Tim::withCount('users')->get();
        return view('admin.tims.index', compact('tims'));
    }

    public function create()
    {
        $users = User::where('is_approved', true)->orderBy('name')->get();
        return view('admin.tims.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tim' => 'required|string|unique:tims,nama_tim',
            'nomor_sk' => 'nullable|string',
            'tanggal_sk' => 'nullable|date',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $tim = Tim::create($request->only('nama_tim', 'nomor_sk', 'tanggal_sk'));
        if ($request->has('users')) {
            $tim->users()->sync($request->users);
        }

        return redirect()->route('admin.tims.index')->with('success', 'Tim baru berhasil dibuat.');
    }

    public function edit(Tim $tim)
    {
        $users = User::where('is_approved', true)->orderBy('name')->get();
        $tim->load('users');
        return view('admin.tims.edit', compact('tim', 'users'));
    }

    public function update(Request $request, Tim $tim)
    {
        $request->validate([
            'nama_tim' => 'required|string|unique:tims,nama_tim,' . $tim->id,
            'nomor_sk' => 'nullable|string',
            'tanggal_sk' => 'nullable|date',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $tim->update($request->only('nama_tim', 'nomor_sk', 'tanggal_sk'));
        $tim->users()->sync($request->users ?? []);

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Tim $tim)
    {
        $tim->delete();
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil dihapus.');
    }
}

