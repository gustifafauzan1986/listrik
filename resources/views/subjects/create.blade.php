@section('title', 'Tambah Mata Pelajaran')
<x-app-layout>
    <div class="page-content">
            <div class="col-md-12 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">Tambah Mata Pelajaran</div>
                    <div class="card-body">
                        <form action="{{ route('subjects.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="fw-bold">Nama Mata Pelajaran</label>
                                <input id="name" type="text" name="name" class="form-control" 
                                    placeholder="Contoh: Pendidikan Agama Islam" required>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Kode Mata Pelajaran</label>
                                <input id="code" type="text" name="code" class="form-control" 
                                    placeholder="Otomatis (Contoh: PAI)" required>
                                <small class="text-muted">Kode akan terisi otomatis, namun tetap bisa diedit manual.</small>
                            </div>

                            <div class="mb-3">
                               <label for="major_id" class="block text-gray-700 font-bold mb-2">Jurusan (Opsional)</label>
                                <select name="major_id" id="major_id" class="form-control">
                                    
                                    <option value="">-- Umum / Muatan Nasional --</option>
                                    
                                    @foreach($majors as $major)
                                        <option value="{{ $major->id }}" {{ old('major_id') == $major->id ? 'selected' : '' }}>
                                            {{ $major->name }} ({{ $major->code ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-gray-500 text-xs mt-1">Biarkan kosong jika ini adalah mata pelajaran umum.</p>
                            </div>


                            <div class="d-flex justify-content-between">
                                <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
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

    {{-- SCRIPT GENERATE KODE OTOMATIS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            // Cek apakah elemen ada sebelum menjalankan script
            if(nameInput && codeInput) {
                nameInput.addEventListener('input', function() {
                    let text = this.value;

                    // Logika: Ambil huruf pertama (\w) setelah batas kata (\b)
                    // Contoh: "Bahasa Indonesia" -> matches dapat ["B", "I"]
                    let matches = text.match(/\b(\w)/g);

                    if (matches) {
                        // Gabungkan huruf dan jadikan Huruf Besar
                        // Hasil: "BI"
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