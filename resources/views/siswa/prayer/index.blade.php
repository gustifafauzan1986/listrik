@section('title', 'Jadwal & Absensi Sholat')

<x-app-layout>
    <style>
        .icon-box { width: 50px; text-align: center; }
        .list-group-item { border-left: 4px solid transparent; transition: all 0.3s; }
        .list-group-item:hover { background-color: #f8f9fa; }
        .list-group-item.active-prayer { border-left-color: #0d6efd; background-color: #e7f1ff; }

        /* CSS Map Leaflet */
        .leaflet-container { font-family: inherit; }
    </style>

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <!-- HEADER WAKTU -->
                <div class="mb-4 text-white shadow-lg card bg-primary">
                    <div class="p-4 text-center card-body">
                        <h5 class="mb-1 text-white-50">Jadwal Sholat Hari Ini</h5>
                        <h2 class="mb-0 fw-bold">Masjid Sekolah</h2>
                        <div class="px-3 py-2 mt-3 bg-white badge text-primary fs-6">
                            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>

                <!-- NOTIFIKASI ERROR/SUCCESS (SESSION) -->
                @if(session('success'))
                    <div class="mb-4 alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- DAFTAR JADWAL SHOLAT -->
                <div class="shadow-sm card">
                    <div class="list-group list-group-flush">
                        @php
                            $prayers = [
                                'subuh'   => 'Subuh',
                                'dhuha'   => 'Dhuha (Sunnah)',
                                'dzuhur'  => 'Dzuhur',
                                'ashar'   => 'Ashar',
                                'maghrib' => 'Maghrib',
                                'isya'    => 'Isya',
                            ];
                            $currentTime = \Carbon\Carbon::now()->format('H:i');

                            // Deteksi sholat aktif sederhana untuk highlight UI
                            $hour = \Carbon\Carbon::now()->hour;
                            $activePrayer = 'subuh';
                            if ($hour >= 5 && $hour < 11) $activePrayer = 'dhuha';
                            elseif ($hour >= 11 && $hour < 15) $activePrayer = 'dzuhur';
                            elseif ($hour >= 15 && $hour < 18) $activePrayer = 'ashar';
                            elseif ($hour >= 18 && $hour < 19) $activePrayer = 'maghrib';
                            elseif ($hour >= 19) $activePrayer = 'isya';
                        @endphp

                        @foreach($prayers as $key => $label)
                            {{-- Skip jika key tidak ada di schedule (misal data API error) --}}
                            @if(!array_key_exists($key, $schedule) && $key != 'dhuha') @continue @endif

                            @php
                                $time = $schedule[$key] ?? '07:00';
                                $isDone = isset($attendances[$key]);

                                // Logic Tombol Absen Aktif:
                                // 1. Waktu sekarang >= Waktu Sholat
                                // 2. ATAU Dhuha (bebas dari pagi)
                                $isActiveTime = ($currentTime >= $time) || $key == 'dhuha';

                                // Highlight sholat yang sedang berlangsung
                                $isCurrent = ($activePrayer == $key);
                            @endphp

                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center {{ $isDone ? 'bg-light' : '' }} {{ $isCurrent && !$isDone ? 'active-prayer' : '' }}">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3 {{ $isDone ? 'text-success' : ($isCurrent ? 'text-primary' : 'text-secondary') }}">
                                        @if($key == 'subuh' || $key == 'isya') <i class="fas fa-moon fa-2x"></i>
                                        @elseif($key == 'maghrib') <i class="fas fa-cloud-sun fa-2x"></i>
                                        @else <i class="fas fa-sun fa-2x"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold {{ $isDone ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $label }}
                                        </h5>
                                        <span class="text-muted small">
                                            <i class="far fa-clock"></i> {{ $time }} WIB
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    @if($isDone)
                                        <button class="px-3 btn btn-sm btn-success disabled rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i> Selesai
                                        </button>
                                        <div class="text-end" style="font-size: 0.7rem; color: #888;">
                                            {{ \Carbon\Carbon::parse($attendances[$key])->format('H:i') }}
                                        </div>
                                    @else
                                        @if($isActiveTime)
                                            <button onclick="handleAbsensi('{{ $key }}', '{{ $label }}')" class="px-3 shadow-sm btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fas fa-map-marker-alt me-1"></i> Absen
                                            </button>
                                        @else
                                            <button class="px-3 btn btn-sm btn-light text-muted disabled rounded-pill">
                                                Belum Waktu
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 text-center text-muted small">
                    <p><i class="fas fa-info-circle me-1"></i> Absensi hanya dapat dilakukan di area Masjid Sekolah.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Template Map untuk SweetAlert -->
    <template id="map-template">
        <div id="location-info" class="mb-2 text-start">
            <!-- Status Text -->
            <div id="status-text" class="p-2 mb-2 badge bg-warning text-dark w-100 text-wrap" style="font-size: 0.85rem;">
                <i class="fas fa-spinner fa-spin me-2"></i>Mencari lokasi & menghitung jarak...
            </div>

            <!-- Map Container -->
            <div id="map-canvas" style="height: 250px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>

            <div class="mt-2 text-center text-muted fst-italic" style="font-size: 0.75rem;">
                *Pastikan posisi Anda berada di dalam lingkaran hijau (Radius {{ \App\Http\Controllers\PrayerController::RADIUS_METER }}m)
            </div>
        </div>
    </template>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // --- KONFIGURASI SESUAI CONTROLLER ---
        // Koordinat Masjid (Harus sama persis dengan PrayerController.php)
        const masjidLat = -0.305123;
        const masjidLng = 100.369456;
        const maxRadius = 50; // meter

        let map;
        let userMarker;
        let masjidCircle;

        function handleAbsensi(prayerKey, label) {
            Swal.fire({
                title: 'Absen Sholat ' + label,
                html: document.getElementById('map-template').innerHTML,
                showCancelButton: true,
                confirmButtonText: 'Kirim Absen',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false,
                didOpen: () => {
                    // Inisialisasi map setelah modal terbuka
                    initMap();
                },
                preConfirm: () => {
                    // Ambil nilai dari hidden input yang di-inject oleh initMap
                    const lat = document.getElementById('lat_val')?.value;
                    const lng = document.getElementById('lng_val')?.value;
                    const dist = document.getElementById('dist_val')?.value;

                    // Validasi Dasar: Apakah GPS sudah dapat?
                    if (!lat || !lng) {
                        Swal.showValidationMessage('Tunggu hingga lokasi ditemukan!');
                        return false;
                    }

                    // Validasi Frontend: Geofencing
                    if (parseInt(dist) > maxRadius) {
                         Swal.showValidationMessage(`Gagal! Anda terlalu jauh (${dist}m). Harap masuk ke area masjid (< ${maxRadius}m)`);
                         return false;
                    }

                    return { lat, lng };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitPrayer(prayerKey, result.value.lat, result.value.lng);
                }
            });
        }

        function initMap() {
            // Delay sedikit agar container render sempurna
            setTimeout(() => {
                // 1. Setup Map
                map = L.map('map-canvas').setView([masjidLat, masjidLng], 18);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                // 2. Marker Masjid
                L.marker([masjidLat, masjidLng]).addTo(map)
                    .bindPopup("<b>Masjid Sekolah</b><br>Pusat Absensi").openPopup();

                // 3. Lingkaran Radius (Zona Hijau)
                masjidCircle = L.circle([masjidLat, masjidLng], {
                    color: 'green',
                    fillColor: '#2ecc71',
                    fillOpacity: 0.2,
                    radius: maxRadius
                }).addTo(map);

                // 4. Deteksi Lokasi User
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const userLoc = [lat, lng];

                            // Tambahkan Marker User (Merah)
                            if (userMarker) map.removeLayer(userMarker);
                            userMarker = L.marker(userLoc, {
                                icon: L.icon({
                                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34]
                                })
                            }).addTo(map).bindPopup("Lokasi Anda").openPopup();

                            // Fit Bounds agar kedua marker terlihat
                            const group = new L.featureGroup([
                                L.marker([masjidLat, masjidLng]),
                                L.marker(userLoc)
                            ]);
                            map.fitBounds(group.getBounds().pad(0.2));

                            // Hitung Jarak & Update UI
                            const distance = calculateDistance(lat, lng, masjidLat, masjidLng);
                            updateStatusUI(distance, lat, lng);
                        },
                        (error) => {
                            showErrorUI(error.message);
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                    Swal.showValidationMessage('Browser tidak mendukung Geolocation');
                }
            }, 300);
        }

        // Fungsi Update UI Status Jarak
        function updateStatusUI(distance, lat, lng) {
            const statusBox = document.getElementById('status-text');
            const container = document.getElementById('location-info');

            // Reset input hidden lama
            const oldLat = document.getElementById('lat_val'); if(oldLat) oldLat.remove();
            const oldLng = document.getElementById('lng_val'); if(oldLng) oldLng.remove();
            const oldDist = document.getElementById('dist_val'); if(oldDist) oldDist.remove();

            if (distance <= maxRadius) {
                // Dalam Radius
                statusBox.className = "badge bg-success w-100 p-2 mb-2";
                statusBox.innerHTML = `<i class="fas fa-check-circle me-2"></i>Dalam Radius: ${distance}m (Aman)`;
            } else {
                // Luar Radius
                statusBox.className = "badge bg-danger w-100 p-2 mb-2";
                statusBox.innerHTML = `<i class="fas fa-times-circle me-2"></i>Di Luar Radius: ${distance}m (Max ${maxRadius}m)`;
            }

            // Inject Hidden Input untuk diambil preConfirm
            container.innerHTML += `
                <input type="hidden" id="lat_val" value="${lat}">
                <input type="hidden" id="lng_val" value="${lng}">
                <input type="hidden" id="dist_val" value="${distance}">
            `;
        }

        function showErrorUI(msg) {
            const statusBox = document.getElementById('status-text');
            if(statusBox) {
                statusBox.className = "badge bg-danger w-100 p-2 mb-2";
                statusBox.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>GPS Error: ${msg}`;
            }
        }

        // Rumus Haversine untuk hitung jarak (Meter)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Radius bumi (meter)
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return Math.round(R * c);
        }

        // Kirim Data ke Controller
        function submitPrayer(prayerName, lat, lng) {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('student.prayer.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    prayer_name: prayerName,
                    latitude: lat,
                    longitude: lng
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Alhamdulillah!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                    Swal.fire('Gagal', msg, 'error');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
