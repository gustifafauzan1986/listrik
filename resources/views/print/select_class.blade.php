<div class="container">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-id-card me-2"></i> Cetak Kartu Identitas Siswa</h3>
        <div>
            <a href="{{ route('print.all') }}" class="btn btn-warning" target="_blank">
                <i class="fas fa-print me-1"></i> Cetak Semua (Satu Sekolah)
            </a>
        </div>
    </div>

    <div class="row">
        @foreach($classrooms as $class)
        <div class="mb-4 col-md-4">
            <div class="shadow-sm card h-100 border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 font-weight-bold text-primary">{{ $class->name }}</h5>
                            <div class="text-muted small">
                                <i class="fas fa-users me-1"></i> {{ $class->students_count }} Siswa
                            </div>
                        </div>
                        <div>
                            @if($class->students_count > 0)
                                <a href="{{ route('print.class', $class->id) }}" target="_blank" class="shadow btn btn-sm btn-primary">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary" disabled>Kosong</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
