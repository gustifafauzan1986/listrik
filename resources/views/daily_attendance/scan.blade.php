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

                    </div>
                </div>

            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let html5QrcodeScanner;

    function onScanSuccess(decodedText, decodedResult) {
        try { html5QrcodeScanner.pause(); } catch(e){}

        Swal.fire({
            title: 'Memproses...',
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
                    }).then(() => { html5QrcodeScanner.resume(); });
                } else {
                    Swal.fire('Gagal', res.message, 'error')
                        .then(() => { html5QrcodeScanner.resume(); });
                }
            },
            error: function(err) {
                Swal.fire('Error', 'Siswa tidak ditemukan / Gangguan Server', 'warning')
                    .then(() => { html5QrcodeScanner.resume(); });
            }
        });
    }

    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, () => {});
</script>
</x-app-layout>
