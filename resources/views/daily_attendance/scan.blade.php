
@section('title')
   Scan Datanng dan Pulang
@endsection
<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="shadow card border-bottom-primary">
                    <div class="text-center text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-school me-2"></i> SCANNER GERBANG</h4>
                        <small>Absensi Harian (Datang & Pulang)</small>
                    </div>
                    <div class="text-center card-body bg-light">

                        <div id="reader" style="width: 100%;"></div>

                        <div class="mt-4">
                            <p class="text-muted small">Scan QR Siswa saat tiba dan saat pulang.</p>
                        </div>

                        <!-- Tombol Test Suara (Opsional, untuk cek speaker) -->
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="playSound('success')">
                                <i class="fas fa-volume-up"></i> Test Suara
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

<!-- Load jQuery DULUAN (Wajib untuk $) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        let html5QrcodeScanner;

        // --- FUNGSI AUDIO GENERATOR (TANPA FILE MP3) ---
        function playSound(status) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return; // Browser tidak support
                
                const ctx = new AudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                if (status === 'success') {
                    // SUARA BERHASIL: "Tinggg" (High Pitch Sine Wave)
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.2);
                    
                    gain.gain.setValueAtTime(0.1, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.2);
                } else {
                    // SUARA GAGAL: "Buzz/Tettt" (Low Pitch Sawtooth Wave)
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(150, ctx.currentTime); // Nada rendah
                    osc.frequency.linearRampToValueAtTime(100, ctx.currentTime + 0.3);
                    
                    gain.gain.setValueAtTime(0.1, ctx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch(e) {
                console.error("Audio Error:", e);
            }
        }
        
        // Expose function ke global agar tombol Test bisa akses
        window.playSound = playSound;

        function onScanSuccess(decodedText, decodedResult) {
            try { html5QrcodeScanner.pause(); } catch(e){}

            Swal.fire({
                title: 'Memproses...',
                text: 'Mencatat kehadiran...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('daily.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    nis: decodedText
                },
                success: function(res) {
                    if(res.status == 'success') {
                        let color = res.type == 'in' ? '#28a745' : '#17a2b8'; // Hijau Masuk, Biru Pulang
                        Swal.fire({
                            title: res.message,
                            text: res.student,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            background: '#fff',
                            iconColor: color
                        }).then(() => {
                            try { html5QrcodeScanner.resume(); } catch(e){}
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error')
                            .then(() => {
                                try { html5QrcodeScanner.resume(); } catch(e){}
                            });
                    }
                },
                error: function(xhr) {
                    // --- PERBAIKAN DEBUGGING ERROR 500 ---
                    console.error("Full Error Log:", xhr);

                    let title = 'Error Sistem';
                    let msg = 'Terjadi kesalahan pada server.';

                    // Ambil pesan spesifik dari Laravel jika ada (misal: "Class not found")
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                        // Tampilkan detail file jika mode debug aktif
                        if (xhr.responseJSON.file) {
                            console.log("File:", xhr.responseJSON.file, "Line:", xhr.responseJSON.line);
                        }
                    } else if (xhr.status === 500) {
                        msg = "Internal Server Error (500). Cek Terminal/Log Laravel Anda untuk detailnya.";
                    } else if (xhr.status === 404) {
                        msg = "Route tidak ditemukan (404). Pastikan route 'daily.store' sudah ada.";
                    }

                    Swal.fire({
                        title: title,
                        text: msg,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        try { html5QrcodeScanner.resume(); } catch(e){}
                    });
                }
            });
        }

        // Pastikan elemen reader ada sebelum init
        if (document.getElementById('reader')) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
            html5QrcodeScanner.render(onScanSuccess, () => {});
        } else {
            console.error("Elemen #reader tidak ditemukan!");
        }
    });
</script>

</x-app-layout>
