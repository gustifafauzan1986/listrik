<!DOCTYPE html>
<html>
<head>
    <title>Buat Jadwal Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Buat Jadwal Pelajaran Baru</h4>
                </div>
                <div class="card-body">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('schedule.store') }}" method="POST">
                        @csrf
                        
                        <!-- Nama Mata Pelajaran -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mata Pelajaran</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="Contoh: Matematika Wajib" required>
                        </div>

                        <!-- Pilih Kelas (Dari Database Classroom) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kelas</label>
                            <select name="classroom_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                @foreach($classrooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih Hari -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hari</label>
                            <select name="day" class="form-select" required>
                                @php
                                    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                    // Otomatis pilih hari ini
                                    $today = \Carbon\Carbon::now()->isoFormat('dddd'); 
                                @endphp
                                @foreach($days as $day)
                                    <option value="{{ $day }}" {{ $day == $today ? 'selected' : '' }}>{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <!-- Jam Mulai -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Mulai</label>
                                <input type="time" name="start_time" class="form-control" value="{{ date('H:i') }}" required>
                            </div>

                            <!-- Jam Selesai -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Selesai</label>
                                <input type="time" name="end_time" class="form-control" value="{{ date('H:i', strtotime('+1 hour')) }}" required>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Jadwal
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>