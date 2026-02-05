<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PrayerSchedule;
use App\Models\PrayerAttendance; // Tambahan untuk pullAttendance
use App\Models\Student;          // Tambahan untuk pullAttendance
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Http; // Dihapus karena diganti cURL Native

class PrayerSettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Lokasi Masjid & Sinkronisasi
     */
    public function index()
    {
        // Ambil data setting atau gunakan default
        $lat    = Setting::value('masjid_lat', -0.305123);
        $lng    = Setting::value('masjid_lng', 100.369456);
        $radius = Setting::value('masjid_radius', 50);
        $cityId = Setting::value('prayer_city_id', '0306'); // Default Bukittinggi

        // Setting Sinkronisasi Antar Server (Baru)
        $myServerKey    = Setting::value('server_sync_key', \Illuminate\Support\Str::random(32)); // Key lokal
        $targetUrl      = Setting::value('target_sync_url'); // URL Server Sumber
        $targetKey      = Setting::value('target_sync_key'); // Key Server Sumber

        // Data untuk dropdown bulan/tahun sinkronisasi
        $years = range(Carbon::now()->year, Carbon::now()->year + 1);
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.prayer.settings', compact('lat', 'lng', 'radius', 'cityId', 'years', 'months', 'myServerKey', 'targetUrl', 'targetKey'));
    }

    /**
     * Simpan Pengaturan Lokasi & ID Kota & Server Sync
     */
    public function update(Request $request)
    {
        $request->validate([
            'masjid_lat'    => 'required|numeric',
            'masjid_lng'    => 'required|numeric',
            'masjid_radius' => 'required|numeric|min:5|max:1000',
            'prayer_city_id'=> 'required|string',
        ]);

        // Simpan ke tabel settings
        Setting::updateOrCreate(['key' => 'masjid_lat'], ['value' => $request->masjid_lat]);
        Setting::updateOrCreate(['key' => 'masjid_lng'], ['value' => $request->masjid_lng]);
        Setting::updateOrCreate(['key' => 'masjid_radius'], ['value' => $request->masjid_radius]);
        Setting::updateOrCreate(['key' => 'prayer_city_id'], ['value' => $request->prayer_city_id]);

        // Simpan Konfigurasi Sync Server (Baru)
        if ($request->has('server_sync_key')) {
            Setting::updateOrCreate(['key' => 'server_sync_key'], ['value' => $request->server_sync_key]);
        }
        if ($request->has('target_sync_url')) {
            Setting::updateOrCreate(['key' => 'target_sync_url'], ['value' => $request->target_sync_url]);
        }
        if ($request->has('target_sync_key')) {
            Setting::updateOrCreate(['key' => 'target_sync_key'], ['value' => $request->target_sync_key]);
        }

        return redirect()->back()->with('success', 'Semua pengaturan berhasil diperbarui!');
    }

    /**
     * Fitur Sinkronisasi Jadwal ke Database (MyQuran)
     */
    public function sync(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
            'month' => 'required|numeric|min:1|max:12',
        ]);

        $cityId = Setting::value('prayer_city_id', '0306');
        $year = $request->year;
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);

        try {
            $url = "https://api.myquran.com/v2/sholat/jadwal/$cityId/$year/$month";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode == 200 && $responseBody) {
                $data = json_decode($responseBody, true);

                if (isset($data['data']['jadwal'])) {
                    $schedules = $data['data']['jadwal'];
                    $count = 0;

                    foreach ($schedules as $sched) {
                        PrayerSchedule::updateOrCreate(
                            ['date' => $sched['date']],
                            [
                                'subuh'   => $sched['subuh'],
                                'dhuha'   => $sched['dhuha'],
                                'dzuhur'  => $sched['dzuhur'],
                                'ashar'   => $sched['ashar'],
                                'maghrib' => $sched['maghrib'],
                                'isya'    => $sched['isya'],
                            ]
                        );
                        $count++;
                    }

                    return redirect()->back()->with('success', "Berhasil sinkronisasi $count data jadwal sholat untuk periode $month/$year.");
                } else {
                    return redirect()->back()->with('error', 'Format data API MyQuran tidak sesuai.');
                }
            } else {
                return redirect()->back()->with('error', "Gagal menghubungi API MyQuran. Status: $httpCode");
            }

        } catch (\Exception $e) {
            Log::error("Sync Prayer Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * BARU: Tarik Data Absensi dari Server Lain
     * MENGGUNAKAN CURL NATIVE (Untuk menghindari error 'Undefined method successful')
     */
    public function pullAttendance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $targetUrl = Setting::value('target_sync_url');
        $targetKey = Setting::value('target_sync_key');

        if (!$targetUrl || !$targetKey) {
            return redirect()->back()->with('error', 'URL atau Key Server Sumber belum dikonfigurasi. Silakan isi di form pengaturan.');
        }

        try {
            // Pastikan URL valid dan arahkan ke endpoint export
            // Hapus trailing slash jika ada, lalu tambah path api
            $endpoint = rtrim($targetUrl, '/') . '/api/prayer/sync-export';

            // Bangun query string
            $queryParams = http_build_query([
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'key'        => $targetKey // Fallback jika header tidak terbaca di beberapa server
            ]);

            $urlWithParams = $endpoint . '?' . $queryParams;

            // Inisialisasi cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlWithParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout lebih lama untuk data banyak

            // Bypass SSL untuk menghindari error sertifikat
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            // Set Headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Server-Key: ' . $targetKey,
                'Accept: application/json'
            ]);

            // User Agent
            curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel App Sync Client');

            // Eksekusi
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Proses Response
            if ($httpCode == 200 && $responseBody) {
                $result = json_decode($responseBody, true);

                if (isset($result['status']) && $result['status'] === 'success') {
                    $attendances = $result['data'];
                    $importedCount = 0;
                    $skippedCount = 0;

                    foreach ($attendances as $row) {
                        // Cari siswa lokal berdasarkan NIS (Pencocokan Data)
                        // Pastikan table students punya kolom 'nis'
                        $localStudent = Student::where('nis', $row['nis'])->first();

                        if ($localStudent) {
                            // Update atau Buat Data Absensi
                            PrayerAttendance::updateOrCreate(
                                [
                                    'student_id'  => $localStudent->id,
                                    'date'        => $row['date'],
                                    'prayer_name' => $row['prayer_name']
                                ],
                                [
                                    'check_in_time' => $row['check_in_time'],
                                    'status'        => $row['status'],
                                    'latitude'      => $row['latitude'],
                                    'longitude'     => $row['longitude'],
                                    // Timestamp bisa diupdate atau biarkan default
                                ]
                            );
                            $importedCount++;
                        } else {
                            $skippedCount++; // Siswa tidak ditemukan di database lokal
                        }
                    }

                    $msg = "Berhasil menarik $importedCount data absensi.";
                    if ($skippedCount > 0) $msg .= " ($skippedCount data dilewati karena NIS siswa tidak ditemukan di server ini).";

                    return redirect()->back()->with('success', $msg);
                } else {
                    $errorMsg = $result['message'] ?? 'Unknown Error from Source Server';
                    return redirect()->back()->with('error', 'Server Sumber menolak: ' . $errorMsg);
                }
            } else {
                return redirect()->back()->with('error', "Gagal koneksi ke server sumber. HTTP Status: $httpCode. Error: $curlError");
            }

        } catch (\Exception $e) {
            Log::error("Server Pull Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menarik data: ' . $e->getMessage());
        }
    }
}
