
<x-app-layout>

        <div class="container mt-5">
            <div class="col-md-6 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">Tambah Mata Pelajaran</div>
                    <div class="card-body">
                        <form action="{{ route('subjects.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="fw-bold">Nama Mata Pelajaran</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Bahasa Indonesia" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Batal</a>
                                <button class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
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
