@section('title')
   Data Seluruh Murid
@endsection

<x-app-layout>
    <div class="page-content">

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="border-0 shadow card">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> Profil Saya</h5>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-4 text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-graduate fa-3x text-secondary"></i>
                            </div>
                            <h4 class="mt-2 fw-bold">{{ $student->name }}</h4>
                            <span class="badge bg-info text-dark">{{ $student->classroom->name ?? 'Tanpa Kelas' }}</span>
                            <div class="mt-1 text-muted small">NIS: {{ $student->nis }}</div>
                        </div>

                        <form action="{{ route('student.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor WhatsApp (Aktif)</label>
                                <input type="number" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" placeholder="Contoh: 08123456789" required>
                                <div class="form-text text-muted">
                                    Nomor ini digunakan untuk menerima notifikasi kehadiran otomatis.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat Rumah</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap...">{{ old('address', $student->address) }}</textarea>
                            </div>

                            <div class="mt-4 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
