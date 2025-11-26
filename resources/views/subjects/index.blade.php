<!DOCTYPE html>
<html>
<head>
    <title>Data Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="mb-4 d-flex justify-content-between">
        <h3>Master Data Mata Pelajaran</h3>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('subjects.create') }}" class="btn btn-primary">+ Tambah Mapel</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="shadow card">
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Mata Pelajaran</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $key => $sub)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-bold">{{ $sub->name }}</td>
                        <td>
                            <form action="{{ route('subjects.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Hapus mapel ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
