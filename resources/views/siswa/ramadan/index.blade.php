@section('title', 'Jurnal Ramadhan')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold text-success"><i class="fas fa-moon me-2"></i>Jurnal Ramadhan</h4>
                <p class="text-muted small mb-0">Catat amalan harianmu di bulan suci.</p>
            </div>
            <div class="badge bg-success p-2">
                <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="row">
            <!-- KOLOM KIRI: FORM INPUT -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm border-0 border-top border-success border-3 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-edit me-2 text-success"></i>Isi Agenda Hari Ini</h6>
                    </div>
                    <div class="card-body">
                        @if($todayEntry)
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h5 class="fw-bold mt-3">Jurnal Terisi</h5>
                                <p class="text-muted">Kamu sudah mengisi jurnal hari ini.</p>
                                <span class="badge bg-success rounded-pill">Tetap Istiqomah!</span>
                            </div>
                        @else
                            <form action="{{ route('student.ramadan.store') }}" method="POST">
                                @csrf
                                
                                <!-- 1. PUASA -->
                                <div class="mb-3">
                                    <label class="fw-bold small text-muted text-uppercase mb-2">Status Puasa</label>
                                    <select name="fasting_status" class="form-select">
                                        <option value="full">Puasa Penuh</option>
                                        <option value="half">Puasa Setengah Hari</option>
                                        <option value="none">Tidak Puasa (Berhalangan)</option>
                                    </select>
                                </div>

                                <!-- 2. SHOLAT WAJIB -->
                                <div class="mb-3">
                                    <label class="fw-bold small text-muted text-uppercase mb-2">Sholat Fardhu</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="prayer_subuh" id="subuh" value="1">
                                            <label class="form-check-label" for="subuh">Subuh</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="prayer_dzuhur" id="dzuhur" value="1">
                                            <label class="form-check-label" for="dzuhur">Dzuhur</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="prayer_ashar" id="ashar" value="1">
                                            <label class="form-check-label" for="ashar">Ashar</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="prayer_maghrib" id="maghrib" value="1">
                                            <label class="form-check-label" for="maghrib">Maghrib</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="prayer_isya" id="isya" value="1">
                                            <label class="form-check-label" for="isya">Isya</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. IBADAH SUNNAH -->
                                <div class="mb-3">
                                    <label class="fw-bold small text-muted text-uppercase mb-2">Ibadah Sunnah</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="prayer_tarawih" id="tarawih">
                                                <label class="form-check-label" for="tarawih">Tarawih</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="prayer_witir" id="witir">
                                                <label class="form-check-label" for="witir">Witir</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="prayer_dhuha" id="dhuha">
                                                <label class="form-check-label" for="dhuha">Dhuha</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="prayer_tahajud" id="tahajud">
                                                <label class="form-check-label" for="tahajud">Tahajjud</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. TADARUS -->
                                <div class="mb-3 p-3 bg-light rounded">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="read_quran" id="quran" onchange="toggleQuranInput(this)">
                                        <label class="form-check-label fw-bold" for="quran">Membaca Al-Qur'an (Tadarus)</label>
                                    </div>
                                    <div id="quran-inputs" style="display:none;">
                                        <input type="text" name="surah_name" class="form-control form-control-sm mb-2" placeholder="Nama Surat (Contoh: Al-Baqarah)">
                                        <input type="text" name="ayat_range" class="form-control form-control-sm" placeholder="Ayat (Contoh: 1-50)">
                                    </div>
                                </div>

                                <!-- 5. CATATAN -->
                                <div class="mb-3">
                                    <label class="fw-bold small text-muted mb-1">Ringkasan Ceramah / Catatan</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Tulis ringkasan ceramah tarawih/subuh..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold">
                                    <i class="fas fa-save me-2"></i> Simpan Jurnal
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: STATISTIK & RIWAYAT -->
            <div class="col-md-7">
                
                <!-- Statistik Card -->
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="card bg-success text-white text-center p-2 border-0 shadow-sm">
                            <h3 class="mb-0 fw-bold">{{ $stats['total_fasting'] }}</h3>
                            <small class="text-white-50">Hari Puasa</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-primary text-white text-center p-2 border-0 shadow-sm">
                            <h3 class="mb-0 fw-bold">{{ $stats['total_tarawih'] }}</h3>
                            <small class="text-white-50">Tarawih</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-warning text-dark text-center p-2 border-0 shadow-sm">
                            <h3 class="mb-0 fw-bold">{{ $stats['total_quran'] }}</h3>
                            <small class="text-black-50">Tadarus</small>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-dark">Riwayat Jurnal</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Tanggal</th>
                                        <th>Puasa</th>
                                        <th class="text-center">Wajib</th>
                                        <th class="text-center">Tarawih</th>
                                        <th class="text-center">Quran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($histories as $log)
                                    @php
                                        $wajibCount = ($log->prayer_subuh + $log->prayer_dzuhur + $log->prayer_ashar + $log->prayer_maghrib + $log->prayer_isya);
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fw-bold">{{ $log->date->format('d M') }}</td>
                                        <td>
                                            @if($log->fasting_status == 'full') <span class="badge bg-success">Penuh</span>
                                            @elseif($log->fasting_status == 'half') <span class="badge bg-warning text-dark">Setengah</span>
                                            @else <span class="badge bg-danger">Tidak</span> @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $wajibCount==5 ? 'bg-primary' : 'bg-secondary' }}">{{ $wajibCount }}/5</span>
                                        </td>
                                        <td class="text-center">
                                            {!! $log->prayer_tarawih ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' !!}
                                        </td>
                                        <td class="text-center">
                                            {!! $log->read_quran ? '<i class="fas fa-book-open text-info" title="'.$log->surah_name.'"></i>' : '-' !!}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan jurnal.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleQuranInput(checkbox) {
            const container = document.getElementById('quran-inputs');
            container.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
    @endpush
</x-app-layout>