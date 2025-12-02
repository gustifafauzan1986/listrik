@section('title')
   Data Seluruh Murid
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">
            
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h3 class="fw-bold text-primary"><i class="fas fa-users me-2"></i> Data Seluruh Murid</h3>
                <div>
                    <a href="{{ route('students.import') }}" class="btn btn-success me-2">
                        <i class="fas fa-file-excel me-1"></i> Import Excel
                    </a>
                </div>
            </div>

            <div class="border-0 shadow card">
                <div class="card-body">

                    <!-- Form Pencarian & Filter -->
                    <form action="{{ route('students.index') }}" method="GET" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari Nama atau NIS..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="classroom_id" class="form-select">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classrooms as $class)
                                        <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
				<div class="card">

					<div class="card-body">
						<div class="table-responsive">
							<table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>No. HP Ortu</th>
                                    <th>Wajah</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    <tr>
                                        <td><span class="border badge bg-light text-dark">{{ $student->nis }}</span></td>
                                        <td class="fw-bold">{{ $student->name }}</td>
                                        <td>{{ $student->classroom->name ?? '-' }}</td>
                                        <td>{{ $student->phone ?? '-' }}</td>
                                        <td>
                                            @if($student->face_descriptor)
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Ada</span>
                                            @else
                                                <span class="badge bg-secondary">Belum</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('students.edit', $student->id) }}" class="text-white btn btn-sm btn-warning" title="Edit">Edit
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus siswa ini? Data absensi juga akan terhapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    Hapus<i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-muted">Data siswa tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
						</div>
					</div>

            </div>
        </div>
    </div>
</x-app-layout>
