<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png')}}" type="image/png"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<!--plugins-->
	<link href="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
	<!-- loader-->
	<link href="{{ asset('backend/assets/css/pace.min.css')}}" rel="stylesheet"/>
	<script src="{{ asset('backend/assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{ asset('backend/assets/css/dark-theme.css')}}"/>
	<link rel="stylesheet" href="{{ asset('backend/assets/css/semi-dark.css')}}"/>
	<link rel="stylesheet" href="{{ asset('backend/assets/css/header-colors.css')}}"/>

	<link href="{{ asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >

	<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css" rel="stylesheet">
    <title>SISFO SMK | @yield('title') </title>
</head>

<body>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="shadow card">
                    <div class="text-white card-header bg-dark">
                        Registrasi Wajah: <strong>{{ $student->name }}</strong>
                    </div>
                    <div class="text-center card-body">

                        <!-- Loading Indicator -->
                        <div id="loading" class="alert alert-info">
                            Sedang memuat model AI... Harap tunggu.
                        </div>

                        <!-- Video Preview -->
                        <div class="position-relative d-inline-block">
                            <video id="video" width="480" height="360" autoplay muted style="border-radius: 10px;"></video>
                            <canvas id="overlay" class="top-0 position-absolute start-0"></canvas>
                        </div>

                        <div class="mt-3">
                            <button id="btn-save" class="btn btn-success btn-lg" disabled>
                                <i class="fas fa-save"></i> Simpan Wajah Ini
                            </button>
                            <a href="{{ route('face.index', ['classroom_id' => $student->classroom_id]) }}" class="btn btn-secondary btn-lg">Batal</a>
                        </div>

                        <p class="mt-2 text-muted small">Pastikan wajah terlihat jelas dan pencahayaan cukup.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
@stack('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.all.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Load Face API dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const video = document.getElementById('video');
    const btnSave = document.getElementById('btn-save');
    let detectedDescriptor = null;

    // 1. Load Model AI
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        faceapi.nets.ssdMobilenetv1.loadFromUri('/models') // Lebih akurat dari tiny
    ]).then(startVideo);

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: {} })
            .then(stream => video.srcObject = stream)
            .catch(err => console.error(err));
    }

    video.addEventListener('play', () => {
        const canvas = document.getElementById('overlay');
        const displaySize = { width: video.width, height: video.height };
        faceapi.matchDimensions(canvas, displaySize);

        document.getElementById('loading').style.display = 'none';

        setInterval(async () => {
            // Deteksi wajah
            const detections = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detections) {
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                // Gambar kotak di wajah
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                faceapi.draw.drawDetections(canvas, resizedDetections);

                // Simpan descriptor sementara
                detectedDescriptor = detections.descriptor;
                btnSave.disabled = false; // Aktifkan tombol simpan
                btnSave.innerHTML = '<i class="fas fa-save"></i> Simpan Wajah Ini';
            } else {
                btnSave.disabled = true;
                btnSave.innerHTML = 'Wajah Tidak Terdeteksi';
            }
        }, 500);
    });

    // Simpan ke Database via AJAX
    btnSave.addEventListener('click', () => {
        if(!detectedDescriptor) return;

        // Convert Float32Array ke Array biasa agar bisa jadi JSON
        const descriptorArray = Array.from(detectedDescriptor);

        $.ajax({
            url: "{{ route('face.store', $student->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                descriptor: JSON.stringify(descriptorArray)
            },
            success: function(res) {
                Swal.fire('Berhasil', res.message, 'success').then(() => {
                    window.location.href = "{{ route('face.index', ['classroom_id' => $student->classroom_id]) }}";
                });
            },
            error: function() {
                Swal.fire('Error', 'Gagal menyimpan data wajah.', 'error');
            }
        });
    });
</script>
</body>

</html>
