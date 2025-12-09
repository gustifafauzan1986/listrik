@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Pengaturan Aplikasi</h5>
                    </div>
                    <div class="card-body">
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- NAV TABS -->
                            <ul class="nav nav-tabs mb-4" id="settingTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                                        <i class="fas fa-school me-2"></i> Identitas Sekolah
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button">
                                        <i class="fas fa-paint-brush me-2"></i> Tampilan Aplikasi
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold" id="report-tab" data-bs-toggle="tab" data-bs-target="#report" type="button">
                                        <i class="fas fa-file-pdf me-2"></i> Kertas & Tanda Tangan
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="settingTabsContent">
                                
                                <!-- TAB 1: IDENTITAS SEKOLAH (Kop Surat) -->
                                <div class="tab-pane fade show active" id="general">
                                    <div class="row mb-4 border-bottom pb-4">
                                        <!-- Logo Kiri -->
                                        <div class="col-md-6 text-center border-end">
                                            <label class="form-label fw-bold">Logo Kiri (Kop Surat)</label>
                                            <div class="mb-2 d-flex justify-content-center">
                                                @if(isset($settings['logo_left']) && $settings['logo_left'])
                                                    <img src="{{ asset('storage/'.$settings['logo_left']) }}" style="height: 80px; border: 1px solid #ddd; padding: 5px;">
                                                @else
                                                    <div class="text-muted border p-3 rounded" style="height: 80px; width: 80px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                        <i class="fas fa-image fa-2x text-secondary"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <input type="file" name="logo_left" class="form-control form-control-sm accept-image">
                                        </div>
                                        <!-- Logo Kanan -->
                                        <div class="col-md-6 text-center">
                                            <label class="form-label fw-bold">Logo Kanan (Kop Surat)</label>
                                            <div class="mb-2 d-flex justify-content-center">
                                                @if(isset($settings['logo_right']) && $settings['logo_right'])
                                                    <img src="{{ asset('storage/'.$settings['logo_right']) }}" style="height: 80px; border: 1px solid #ddd; padding: 5px;">
                                                @else
                                                    <div class="text-muted border p-3 rounded" style="height: 80px; width: 80px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                        <i class="fas fa-image fa-2x text-secondary"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <input type="file" name="logo_right" class="form-control form-control-sm accept-image">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Sekolah</label>
                                        <input type="text" name="school_name" class="form-control" value="{{ $settings['school_name'] ?? '' }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Alamat Lengkap</label>
                                        <textarea name="school_address" class="form-control" rows="2">{{ $settings['school_address'] ?? '' }}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">No. Telepon</label>
                                            <input type="text" name="school_phone" class="form-control" value="{{ $settings['school_phone'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Email</label>
                                            <input type="email" name="school_email" class="form-control" value="{{ $settings['school_email'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Website</label>
                                        <input type="text" name="school_web" class="form-control" value="{{ $settings['school_web'] ?? '' }}">
                                    </div>
                                </div>

                                <!-- TAB 2: TAMPILAN APLIKASI (Favicon & Sidebar) -->
                                <div class="tab-pane fade" id="branding">
                                    <div class="row">
                                        <!-- Sidebar Logo -->
                                        <div class="col-md-6 mb-4 text-center">
                                            <label class="form-label fw-bold d-block">Logo Sidebar (Pojok Kiri Atas)</label>
                                            <div class="p-3 bg-primary rounded d-inline-block mb-2">
                                                <!-- Preview di atas background biru (seperti navbar) -->
                                                @if(isset($settings['app_logo']) && $settings['app_logo'])
                                                    <img src="{{ asset('storage/'.$settings['app_logo']) }}" style="height: 40px;">
                                                @else
                                                    <i class="fas fa-qrcode fa-2x text-white"></i>
                                                @endif
                                            </div>
                                            <input type="file" name="app_logo" class="form-control form-control-sm mt-2">
                                            <div class="form-text">Format PNG transparan disarankan. Max 2MB.</div>
                                        </div>

                                        <!-- Favicon -->
                                        <div class="col-md-6 mb-4 text-center">
                                            <label class="form-label fw-bold d-block">Favicon (Browser Tab)</label>
                                            <div class="mb-2">
                                                @if(isset($settings['app_favicon']) && $settings['app_favicon'])
                                                    <img src="{{ asset('storage/'.$settings['app_favicon']) }}" style="height: 32px; width: 32px;">
                                                @else
                                                    <i class="fas fa-globe fa-2x text-secondary"></i>
                                                @endif
                                            </div>
                                            <input type="file" name="app_favicon" class="form-control form-control-sm mt-2">
                                            <div class="form-text">Format ICO/PNG. Ukuran kecil (32x32 px).</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Aplikasi (Di Navbar)</label>
                                        <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] ?? 'E-Absensi' }}">
                                    </div>
                                </div>

                                <!-- TAB 3: KERTAS & TANDA TANGAN -->
                                <div class="tab-pane fade" id="report">
                                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Format Kertas PDF</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Ukuran Kertas</label>
                                            <select name="paper_size" class="form-select">
                                                <option value="a4" {{ ($settings['paper_size'] ?? '') == 'a4' ? 'selected' : '' }}>A4</option>
                                                <option value="f4" {{ ($settings['paper_size'] ?? '') == 'f4' ? 'selected' : '' }}>F4 / Folio</option>
                                                <option value="letter" {{ ($settings['paper_size'] ?? '') == 'letter' ? 'selected' : '' }}>Letter</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Orientasi</label>
                                            <select name="paper_orientation" class="form-select">
                                                <option value="portrait" {{ ($settings['paper_orientation'] ?? '') == 'portrait' ? 'selected' : '' }}>Portrait (Tegak)</option>
                                                <option value="landscape" {{ ($settings['paper_orientation'] ?? '') == 'landscape' ? 'selected' : '' }}>Landscape (Mendatar)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <label class="form-label fw-bold mt-2">Margin (cm)</label>
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <small>Atas</small>
                                            <input type="number" step="0.1" name="margin_top" class="form-control" value="{{ $settings['margin_top'] ?? '2.5' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <small>Bawah</small>
                                            <input type="number" step="0.1" name="margin_bottom" class="form-control" value="{{ $settings['margin_bottom'] ?? '2.5' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <small>Kiri</small>
                                            <input type="number" step="0.1" name="margin_left" class="form-control" value="{{ $settings['margin_left'] ?? '2.5' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <small>Kanan</small>
                                            <input type="number" step="0.1" name="margin_right" class="form-control" value="{{ $settings['margin_right'] ?? '2.5' }}">
                                        </div>
                                    </div>

                                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">Tanda Tangan Laporan</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Kota/Kabupaten</label>
                                            <input type="text" name="signature_city" class="form-control" value="{{ $settings['signature_city'] ?? 'Jakarta' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Jabatan Penandatangan</label>
                                            <input type="text" name="signature_title" class="form-control" value="{{ $settings['signature_title'] ?? 'Kepala Sekolah' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Nama Pejabat</label>
                                            <input type="text" name="signature_name" class="form-control" value="{{ $settings['signature_name'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">NIP / NUPTK</label>
                                            <input type="text" name="signature_nip" class="form-control" value="{{ $settings['signature_nip'] ?? '' }}">
                                        </div>
                                        
                                        <!-- INPUT TANDA TANGAN (GAMBAR) -->
                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Scan Tanda Tangan (Opsional)</label>
                                            <div class="d-flex align-items-center">
                                                @if(isset($settings['signature_image']) && $settings['signature_image'])
                                                    <div class="me-3 border p-1 rounded">
                                                        <img src="{{ asset('storage/'.$settings['signature_image']) }}" style="height: 60px;">
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <input type="file" name="signature_image" class="form-control form-control-sm accept-image">
                                                    <div class="form-text">Upload gambar tanda tangan (PNG transparan) jika ingin muncul otomatis di PDF.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Semua Pengaturan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Cek apakah ada session 'success' yang dikirim dari controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000 // Notifikasi hilang otomatis setelah 2 detik
            });
        @endif

        // Opsional: Cek jika ada error validasi
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>
</x-app-layout>
