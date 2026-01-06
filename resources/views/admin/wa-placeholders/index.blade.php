@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Placeholder WhatsApp</h6>
            <a href="{{ route('admin.wa-placeholders.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Placeholder
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Placeholder (Kode)</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($placeholders as $ph)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $ph->placeholder }}</code></td>
                            <td>{{ $ph->deskripsi }}</td>
                            <td>
                                <a href="{{ route('admin.wa-placeholders.edit', $ph->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.wa-placeholders.destroy', $ph->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus placeholder ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data placeholder.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $placeholders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection