@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-tie me-2"></i> Manajemen Penerima Kuasa</h4>
                </div>
                <div class="card-body">
                    
                    {{-- Pesan Sukses/Error --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Form Tambah --}}
                    <div class="card mb-4 bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Tambah Penerima Kuasa Baru</h6>
                            <form action="{{ route('admin.kuasa.store') }}" method="POST" class="row g-3">
                                @csrf
                                <div class="col-md-3">
                                    <input type="text" name="kode_kuasa" class="form-control" placeholder="Kode (Mis: K-001)" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="nama_kuasa" class="form-control" placeholder="Nama Lengkap" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="nomer_wa" class="form-control" placeholder="Nomor WA (08xxx)" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i> Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Tabel Data --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode</th>
                                    <th>Nama Kuasa</th>
                                    <th width="20%">No. WhatsApp</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kuasas as $index => $kuasa)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center fw-bold">{{ $kuasa->kode_kuasa }}</td>
                                    <td>{{ $kuasa->nama_kuasa }}</td>
                                    <td class="text-center">{{ $kuasa->nomer_wa }}</td>
                                    <td class="text-center">
                                        {{-- Tombol Edit (Modal Trigger) --}}
                                        <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editModal{{ $kuasa->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        {{-- Form Hapus --}}
                                        <form action="{{ route('admin.kuasa.destroy', $kuasa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal Edit --}}
                                <div class="modal fade" id="editModal{{ $kuasa->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Penerima Kuasa</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.kuasa.update', $kuasa->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Kuasa</label>
                                                        <input type="text" name="kode_kuasa" class="form-control" value="{{ $kuasa->kode_kuasa }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Kuasa</label>
                                                        <input type="text" name="nama_kuasa" class="form-control" value="{{ $kuasa->nama_kuasa }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nomor WhatsApp</label>
                                                        <input type="text" name="nomer_wa" class="form-control" value="{{ $kuasa->nomer_wa }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada data penerima kuasa.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection