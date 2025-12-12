@section('title', 'Tambah Mata Pelajaran')
<x-app-layout>
    <div class="page-content">
        <div class="col-md-12 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">Tambah Jurusan</div>
                <div class="card-body">
                    <form action="{{ route('majors.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="fw-bold">Nama Jurusan</label>
                            <input id="name" type="text" name="name" class="form-control" 
                                placeholder="Contoh: Teknik Instalasi Tenaga Listrik" required>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Kode Jurusan</label>
                            <input id="code" type="text" name="code" class="form-control" 
                                placeholder="Otomatis terisi (Contoh: TITL)" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('majors.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- CDN SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- BAGIAN 1: LOGIC GENERATE KODE OTOMATIS ---
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            if(nameInput && codeInput) {
                nameInput.addEventListener('input', function() {
                    let text = this.value;
                    
                    // Regex: \b = batas kata, \w = karakter huruf/angka
                    // Mengambil huruf pertama dari setiap kata
                    let matches = text.match(/\b(\w)/g);
                    
                    if (matches) {
                        // Gabungkan huruf dan jadikan Huruf Besar (UPPERCASE)
                        let acronym = matches.join('').toUpperCase();
                        codeInput.value = acronym;
                    } else {
                        codeInput.value = '';
                    }
                });
            }
        });
    </script>
</x-app-layout>