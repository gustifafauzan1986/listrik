@section('title')
   Data Seluruh Murid
@endsection

<x-app-layout>
    <div class="page-content">

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> Profil Saya</h5>
                
                <!-- TOMBOL CETAK KARTU (BARU) -->
                <a href="{{ route('student.print.card') }}" target="_blank" class="btn btn-light btn-sm text-primary fw-bold">
                    <i class="fas fa-id-card me-1"></i> Cetak Kartu
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                        <i class="fas fa-user-graduate fa-4x text-secondary"></i>
                    </div>
                    <h4 class="mt-3 fw-bold">{{ $students->name }}</h4>
                    <span class="badge bg-info text-dark px-3 py-2">{{ $students->classroom->name ?? 'Tanpa Kelas' }}</span>
                    <div class="text-muted small mt-2">NIS: {{ $students->nis }}</div>
                    
                    <!-- Opsi Tambahan: Tombol Cetak Besar di Tengah -->
                    <div class="mt-3">
                        <a href="{{ route('student.print.card') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-print me-1"></i> Download / Cetak Kartu QR
                        </a>
                    </div>
                </div>

                <hr>

                <form action="{{ route('student.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor WhatsApp (Aktif)</label>
                        <input type="number" name="phone" class="form-control" value="{{ old('phone', $students->phone) }}" placeholder="Contoh: 08123456789" required>
                        <div class="form-text text-muted">
                            <i class="fab fa-whatsapp text-success"></i> Nomor ini digunakan untuk menerima notifikasi kehadiran otomatis.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat Rumah</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap...">{{ old('address', $students->address) }}</textarea>
                    </div>

                    <div class="d-grid mt-4">
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
