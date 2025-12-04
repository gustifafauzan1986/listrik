@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="shadow card">
                                <div class="text-white card-header bg-primary">
                                    <h4 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i> Import Data Guru</h4>
                                </div>
                                <div class="card-body">

                                    @if(session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if(session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif

                                    <div class="alert alert-info border-left-primary">
                                        <strong><i class="fas fa-info-circle"></i> Format Excel Wajib:</strong> <br>
                                        Pastikan header kolom Excel Anda menggunakan nama berikut (huruf kecil):<br>
                                        <code class="text-dark fw-bold">nama</code> |
                                        <code class="text-dark fw-bold">email</code> |
                                        <code class="text-dark fw-bold">password</code> |
                                        <code class="text-dark fw-bold">nip</code> |
                                        <code class="text-dark fw-bold">jk</code> |
                                        <code class="text-dark fw-bold">hp</code>
                                    </div>

                                    <form action="{{ route('teachers.import.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
                                            <input type="file" name="file" class="form-control" required accept=".xlsx, .xls, .csv">
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-1"></i> Kembali
                                            </a>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-upload me-1"></i> Import Guru
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Contoh Tabel Preview -->
                            <div class="mt-4">
                                <h6 class="fw-bold text-secondary">Contoh isi file Excel:</h6>
                                <div class="table-responsive">
                                    <table class="table mt-2 bg-white table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>nama</th>
                                                <th>email</th>
                                                <th>password</th>
                                                <th>nip</th>
                                                <th>jk</th>
                                                <th>hp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Budi Santoso</td>
                                                <td>budi@sekolah.com</td>
                                                <td>rahasia</td>
                                                <td>19800101...</td>
                                                <td>L</td>
                                                <td>0812345678</td>
                                            </tr>
                                            <tr>
                                                <td>Siti Aminah</td>
                                                <td>siti@sekolah.com</td>
                                                <td>guru123</td>
                                                <td>19900202...</td>
                                                <td>P</td>
                                                <td>0898765432</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">* Kolom lain seperti <code>alamat</code>, <code>pendidikan</code> opsional (bisa ditambahkan di Excel).</small>
                            </div>
                        </div>
    </div>
</x-app-layout>
