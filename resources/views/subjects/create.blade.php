<!DOCTYPE html>
<html>
<head>
    <title>Tambah Mapel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="col-md-6 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">Tambah Mata Pelajaran</div>
            <div class="card-body">
                <form action="{{ route('subjects.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="fw-bold">Nama Mata Pelajaran</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Bahasa Indonesia" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
