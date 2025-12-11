@section('title', 'Tambah Mata Pelajaran')
<x-app-layout>
    <div class="page-content">
            <div class="col-md-12 mx-auto">
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
                                <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
                                     <i class="fas fa-arrow-left me-1"></i> Batal
                                </a>
                                <button class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </div>
</x-app-layout>
