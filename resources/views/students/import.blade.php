<x-app-layout>
<div class="page-content">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h4>Import Data Siswa</h4>
                    </div>
                    <div class="card-body">

                        <!-- Alert Biasa (Success/Error System) -->
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Format Excel Wajib:</strong> <br>
                            <code>nis</code> | <code>nama_siswa</code> | <code>kelas</code>
                        </div>

                        <form action="{{ route('students.import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label>Pilih File Excel (.xlsx / .xls)</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-success">Import Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contoh Tabel Preview -->
                <div class="mt-4">
                    <small class="text-muted">Contoh isi file Excel:</small>
                    <table class="table mt-2 table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>nis</th>
                                <th>nama_siswa</th>
                                <th>kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1001</td>
                                <td>Budi Santoso</td>
                                <td>XII RPL 1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Cek apakah ada session error_duplicates dari controller
    @if(session('error_duplicates'))
        let duplicates = @json(session('error_duplicates'));

        // Buat list HTML untuk ditampilkan di popup
        let listHtml = '<ul style="text-align: left; color: #d33; font-size: 14px;">';
        duplicates.forEach(function(item) {
            listHtml += `<li>${item}</li>`;
        });
        listHtml += '</ul>';

        Swal.fire({
            icon: 'warning',
            title: 'Beberapa Data Gagal Diimport!',
            html: `
                <p>NIS berikut sudah terdaftar di sistem dan dilewati:</p>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                    ${listHtml}
                </div>
                <p class="mt-2 text-muted small">Data lainnya berhasil disimpan.</p>
            `,
            confirmButtonText: 'Mengerti'
        });
    @endif
</script>
</x-app-layout>


