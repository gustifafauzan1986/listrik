<x-app-layout>
    <div class="page-content">
            <div class="col-md-12">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h3 class="fw-bold text-primary"><i class="fas fa-users me-2"></i> Setting Sekolah</h3>
                </div>
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-school me-2"></i> Pengaturan Kop Surat & Sekolah</h5>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- PENTING: enctype multipart untuk upload file -->
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- AREA LOGO -->
                            <div class="pb-4 mb-4 row border-bottom">
                                <!-- Logo Kiri -->
                                <div class="text-center col-md-6 border-end">
                                    <label class="form-label fw-bold">Logo Kiri (Utama)</label>
                                    <div class="mb-2 d-flex justify-content-center">
                                        @if(isset($settings['logo_left']) && $settings['logo_left'])
                                            <img src="{{ asset('storage/'.$settings['logo_left']) }}" alt="Logo Kiri" class="img-fluid" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                                        @else
                                            <div class="p-3 border rounded text-muted" style="height: 100px; width: 100px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                <i class="fas fa-image fa-2x text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" name="logo_left" class="form-control form-control-sm accept-image">
                                    <small class="mt-1 text-muted d-block">Format: PNG/JPG (Max 2MB)</small>
                                </div>

                                <!-- Logo Kanan -->
                                <div class="text-center col-md-6">
                                    <label class="form-label fw-bold">Logo Kanan (Opsional)</label>
                                    <div class="mb-2 d-flex justify-content-center">
                                        @if(isset($settings['logo_right']) && $settings['logo_right'])
                                            <img src="{{ asset('storage/'.$settings['logo_right']) }}" alt="Logo Kanan" class="img-fluid" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                                        @else
                                            <div class="p-3 border rounded text-muted" style="height: 100px; width: 100px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                <i class="fas fa-image fa-2x text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" name="logo_right" class="form-control form-control-sm accept-image">
                                    <small class="mt-1 text-muted d-block">Format: PNG/JPG (Max 2MB)</small>
                                </div>
                            </div>

                            <!-- AREA IDENTITAS SEKOLAH -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Sekolah</label>
                                <input type="text" name="school_name" class="form-control"
                                    value="{{ $settings['school_name'] ?? '' }}" placeholder="Contoh: SMK NEGERI 1 JAKARTA" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat Lengkap</label>
                                <textarea name="school_address" class="form-control" rows="2" placeholder="Contoh: Jl. Budi Utomo No. 1, Jakarta Pusat">{{ $settings['school_address'] ?? '' }}</textarea>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">No. Telepon / Fax</label>
                                    <input type="text" name="school_phone" class="form-control"
                                        value="{{ $settings['school_phone'] ?? '' }}" placeholder="(021) 123-4567">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Alamat Email</label>
                                    <input type="email" name="school_email" class="form-control"
                                        value="{{ $settings['school_email'] ?? '' }}" placeholder="info@sekolah.sch.id">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Website Sekolah</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="text" name="school_web" class="form-control"
                                        value="{{ $settings['school_web'] ?? '' }}" placeholder="www.sekolah.sch.id">
                                </div>
                            </div>

                            <div class="alert alert-info small d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <strong>Info:</strong> Data yang Anda simpan di halaman ini akan otomatis muncul pada <b>Kop Surat</b> di semua file Laporan PDF (Absensi Kelas & Absensi Siswa).
                                </div>
                            </div>

                            <div class="mt-4 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

    </div>
</x-app-layout>
