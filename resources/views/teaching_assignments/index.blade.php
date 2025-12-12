@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">
        <div class="container py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Jadwal Mengajar (Mapping)</h1>
                <a href="{{ route('teaching-assignments.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Mapping Baru
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Guru & Mata Pelajaran</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Guru</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Jurusan (Guru)</th>
                                    <th>Tahun Ajaran</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $key => $assignment)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="fw-bold">{{ $assignment->teacher->name ?? 'Guru Terhapus' }}</td>
                                    <td>
                                        {{ $assignment->subject->name ?? 'Mapel Terhapus' }}
                                        @if(optional($assignment->subject)->major)
                                            <span class="badge bg-info text-dark">{{ $assignment->subject->major->code }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $assignment->classroom->name ?? 'Kelas Terhapus' }}</span>
                                    </td>
                                    <td>
                                        @if(optional($assignment->teacher)->major)
                                            <span class="badge bg-primary">{{ $assignment->teacher->major->code }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Umum</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->academic_year ?? '-' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Mapping">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i><br>
                                        Belum ada data jadwal mengajar. Silakan tambahkan mapping baru.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            // Jika menggunakan DataTables (Opsional)
            $(document).ready(function() {
                $('#dataTable').DataTable();
            });
        </script>
        @endpush
    </div>
</x-app-layout>