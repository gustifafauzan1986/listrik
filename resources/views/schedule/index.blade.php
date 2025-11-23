<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Mengajar Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Sistem Absensi</a>
        <span class="navbar-text text-white">
            {{ Auth::user()->name }} | <a href="{{ route('dashboard') }}" class="text-white fw-bold" style="text-decoration: none;">Dashboard</a>
        </span>
    </div>
</nav>

<div class="container">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Jadwal Mengajar Saya</h3>
        <div>
            <!-- TOMBOL TAMBAH JADWAL -->
            <a href="{{ route('schedule.create') }}" class="btn btn-success">
                + Buat Jadwal Baru
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $sched)
                        @php
                            // Logic Status Aktif
                            $dayMap = [
                                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
                            ];
                            $todayIs = $dayMap[date('l')]; 
                            $now = date('H:i:s');
                            
                            $isActive = false;
                            if ($sched->day == $todayIs && $now >= $sched->start_time && $now <= $sched->end_time) {
                                $isActive = true;
                            }
                        @endphp

                        <tr class="{{ $isActive ? 'table-success fw-bold' : '' }}">
                            <td>{{ $sched->day }}</td>
                            <td>{{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $sched->classroom->name ?? 'Tanpa Kelas' }}
                                </span>
                            </td>
                            <td>{{ $sched->subject_name }}</td>
                            <td class="text-center">
                                @if($isActive)
                                    <a href="{{ route('scan.index', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-primary">
                                        Mulai Absen (Scan)
                                    </a>
                                @else
                                    <!-- Tombol Hapus (Hanya muncul jika jadwal tidak aktif) -->
                                    <form action="{{ route('schedule.destroy', $sched->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus jadwal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada jadwal. Silakan klik tombol "Buat Jadwal Baru".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>