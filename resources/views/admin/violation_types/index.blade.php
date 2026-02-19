@section('title', 'Master Jenis Pelanggaran')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary"><i class="fas fa-exclamation-circle me-2"></i>Jenis Pelanggaran</h4>
                <p class="text-muted mb-0">Kelola daftar pelanggaran dan bobot poin siswa.</p>
            </div>
            <a href="{{ route('admin.violation-types.create') }}" class="btn btn-primary shadow-sm fw-bold">
                <i class="fas fa-plus me-1"></i> Tambah Data
            </a>
        </div>

        <!-- Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-0 fw-bold text-dark mt-2">Daftar Pelanggaran</h6>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('admin.violation-types.index') }}" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama pelanggaran..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="40%">Nama Pelanggaran</th>
                                <th class="text-center" width="15%">Kategori</th>
                                <th class="text-center" width="15%">Bobot Poin</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violationTypes as $index => $item)
                                @php
                                    $badgeClass = match($item->category) {
                                        'ringan' => 'bg-success',
                                        'sedang' => 'bg-warning text-dark',
                                        'berat'  => 'bg-danger',
                                        default  => 'bg-secondary'
                                    };
                                @endphp
                            <tr>
                                <td class="ps-4">{{ $violationTypes->firstItem() + $index }}</td>
                                <td class="fw-bold text-dark">{{ $item->name }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} rounded-pill text-uppercase px-3">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger">{{ $item->points }} Poin</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.violation-types.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.violation-types.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <img src="https://illustrations.popsy.co/gray/question-mark.svg" class="w-25 opacity-50 mb-3" alt="Empty">
                                    <br>Belum ada data pelanggaran.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $violationTypes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>