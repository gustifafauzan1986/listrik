<x-app-layout>
    <div class="page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="shadow card">
                        <div class="text-white card-header bg-primary">
                            <h5 class="mb-0"><i class="fas fa-school me-2"></i> Pengaturan Kop Surat & Sekolah</h5>
                        </div>
                        <div class="card-body">

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form action="{{ route('settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Sekolah</label>
                                    <input type="text" name="school_name" class="form-control"
                                        value="{{ $settings['school_name'] ?? '' }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Lengkap</label>
                                    <textarea name="school_address" class="form-control" rows="2">{{ $settings['school_address'] ?? '' }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">No. Telepon / Fax</label>
                                        <input type="text" name="school_phone" class="form-control"
                                            value="{{ $settings['school_phone'] ?? '' }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">Alamat Email</label>
                                        <input type="email" name="school_email" class="form-control"
                                            value="{{ $settings['school_email'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Website Sekolah</label>
                                    <input type="text" name="school_web" class="form-control"
                                        value="{{ $settings['school_web'] ?? '' }}">
                                </div>

                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle"></i> Data ini akan muncul otomatis pada Kop Surat Laporan PDF.
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
