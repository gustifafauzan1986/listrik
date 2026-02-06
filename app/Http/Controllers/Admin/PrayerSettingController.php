<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PrayerSchedule;
use App\Models\PrayerAttendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PrayerSettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Lokasi Masjid & Sinkronisasi
     */
    public function index()
    {
        // 1. Setting Lokasi Masjid (Geofencing)
        $lat    = Setting::value('masjid_lat', -0.305123);
        $lng    = Setting::value('masjid_lng', 100.369456);
        $radius = Setting::value('masjid_radius', 50);
        $cityId = Setting::value('prayer_city_id', '0306'); // Default Bukittinggi

        // 2. Setting Sinkronisasi Antar Server
        // Key Lokal: Digunakan jika server INI menjadi SUMBER data bagi server lain (Server A)
        $myServerKey = Setting::value('server_sync_key', \Illuminate\Support\Str::random(32));

        // Target Server: Digunakan jika server INI mengambil data DARI server lain (Server B)
        // Ini adalah konfigurasi untuk menginput URL dan Key API Server A di Server B
        $targetUrl = Setting::value('target_sync_url');
        $targetKey = Setting::value('target_sync_key');

        // Data untuk dropdown sinkronisasi jadwal sholat
        $years = range(Carbon::now()->year, Carbon::now()->year + 1);
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.prayer.settings', compact('lat', 'lng', 'radius', 'cityId', 'years', 'months', 'myServerKey', 'targetUrl', 'targetKey'));
    }

    /**
     * Simpan Pengaturan Lokasi, ID Kota, & Konfigurasi Server Sync
     */
    public function update(Request $request)
    {
        $request->validate([
            'masjid_lat'    => 'required|numeric',
            'masjid_lng'    => 'required|numeric',
            'masjid_radius' => 'required|numeric|min:5|max:1000',
            'prayer_city_id'=> 'required|string',
            // Validasi input URL dan Key
            'target_sync_url' => 'nullable|url',
            'target_sync_key' => 'nullable|string',
            'server_sync_key' => 'nullable|string',
        ]);

        // Simpan Setting Lokasi & Kota
        Setting::updateOrCreate(['key' => 'masjid_lat'], ['value' => $request->masjid_lat]);
        Setting::updateOrCreate(['key' => 'masjid_lng'], ['value' => $request->masjid_lng]);
        Setting::updateOrCreate(['key' => 'masjid_radius'], ['value' => $request->masjid_radius]);
        Setting::updateOrCreate(['key' => 'prayer_city_id'], ['value' => $request->prayer_city_id]);

        // Simpan Konfigurasi Sync Server

        // 1. Simpan Key Server Sendiri (Untuk Server A / Sumber)
        if ($request->has('server_sync_key')) {
            Setting::updateOrCreate(['key' => 'server_sync_key'], ['value' => $request->server_sync_key]);
        }

        // 2. Simpan URL & Key Server Target (Untuk Server B / Tujuan)
        if ($request->has('target_sync_url')) {
            // Hapus trailing slash agar format URL konsisten
            $cleanUrl = rtrim($request->target_sync_url, '/');
            Setting::updateOrCreate(['key' => 'target_sync_url'], ['value' => $cleanUrl]);
        }

        if ($request->has('target_sync_key')) {
            Setting::updateOrCreate(['key' => 'target_sync_key'], ['value' => $request->target_sync_key]);
        }

        return redirect()->back()->with('success', 'Semua pengaturan berhasil diperbarui!');
    }

    /**
     * Fitur 1: Sinkronisasi Jadwal Sholat dari MyQuran (Public API)
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

            // cURL Request ke MyQuran
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel Prayer Sync');

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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

                    return redirect()->back()->with('success', "Berhasil sinkronisasi $count data jadwal sholat.");
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
     * Fitur 2: Tarik Data Absensi dari Server Lain (Server Tujuan)
     */
    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $targetUrl = Setting::value('target_sync_url');
    //     $targetKey = Setting::value('target_sync_key');

    //     if (!$targetUrl || !$targetKey) {
    //         return redirect()->back()->with('error', 'URL atau Key Server Sumber belum dikonfigurasi. Silakan isi di form pengaturan.');
    //     }

    //     try {
    //         // Bangun URL Endpoint (Server A)
    //         $endpoint = rtrim($targetUrl, '/') . '/api/prayer/sync-export';

    //         // Parameter Query
    //         $queryParams = http_build_query([
    //             'start_date' => $request->start_date,
    //             'end_date'   => $request->end_date,
    //             'key'        => $targetKey // Fallback query param
    //         ]);

    //         $urlWithParams = $endpoint . '?' . $queryParams;

    //         // Inisialisasi cURL
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    //         curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout lebih lama (data bisa banyak)

    //         // Bypass SSL (Opsional, untuk lokal/dev)
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    //         // Set Headers (Auth)
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //             'X-Server-Key: ' . $targetKey,
    //             'Accept: application/json'
    //         ]);

    //         curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel App Sync Client');

    //         // Eksekusi Request
    //         $responseBody = curl_exec($ch);
    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         $curlError = curl_error($ch);
    //         curl_close($ch);

    //         // Proses Response
    //         if ($httpCode == 200 && $responseBody) {
    //             $result = json_decode($responseBody, true);

    //             if (isset($result['status']) && $result['status'] === 'success') {
    //                 $attendances = $result['data'];
    //                 $importedCount = 0;
    //                 $skippedCount = 0;

    //                 foreach ($attendances as $row) {
    //                     // LOGIKA PENTING: Mencocokkan data berdasarkan NIS
    //                     // Pastikan tabel students memiliki kolom 'nis' yang unik
    //                     $localStudent = Student::where('nis', $row['nis'])->first();

    //                     if ($localStudent) {
    //                         // Update atau Buat Data Absensi Baru
    //                         PrayerAttendance::updateOrCreate(
    //                             [
    //                                 'student_id'  => $localStudent->id,
    //                                 'date'        => $row['date'],
    //                                 'prayer_name' => $row['prayer_name']
    //                             ],
    //                             [
    //                                 'check_in_time' => $row['check_in_time'],
    //                                 'status'        => $row['status'],
    //                                 'latitude'      => $row['latitude'],
    //                                 'longitude'     => $row['longitude'],
    //                                 // created_at & updated_at otomatis dihandle Eloquent atau bisa manual
    //                             ]
    //                         );
    //                         $importedCount++;
    //                     } else {
    //                         // Data dilewati jika siswa dengan NIS tersebut tidak ditemukan di server ini
    //                         $skippedCount++;
    //                     }
    //                 }

    //                 $msg = "Berhasil menarik $importedCount data absensi.";
    //                 if ($skippedCount > 0) $msg .= " ($skippedCount data dilewati karena NIS siswa tidak ditemukan).";

    //                 return redirect()->back()->with('success', $msg);
    //             } else {
    //                 $errorMsg = $result['message'] ?? 'Unknown Error from Source Server';
    //                 return redirect()->back()->with('error', 'Server Sumber menolak: ' . $errorMsg);
    //             }
    //         } else {
    //             return redirect()->back()->with('error', "Gagal koneksi ke server sumber. HTTP Status: $httpCode. Error: $curlError");
    //         }

    //     } catch (\Exception $e) {
    //         Log::error("Server Pull Error: " . $e->getMessage());
    //         return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menarik data: ' . $e->getMessage());
    //     }
    // }

        /**
     * Menarik data dari server target (Full Sync)
     */
    public function pullAttendance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        // Ambil config dari table settings (pastikan record ini ada di database)
        $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
        $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

        if (!$targetUrl || !$targetKey) {
            return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
        }

        try {
            // Memanggil HTTP Client Laravel
            // rtrim untuk memastikan tidak ada double slash pada URL
            $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

            $response = Http::withHeaders([
                'X-Api-Key' => $targetKey,
                'Accept' => 'application/json'
            ])->timeout(120)->get($apiUrl, [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'key' => $targetKey
            ]);

            // Periksa kegagalan koneksi (seperti 404, 500, atau timeout)
            if ($response->failed()) {
                Log::error("Sync Failed: " . $response->body());
                return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
            }

            $result = $response->json();

            if (!isset($result['status']) || $result['status'] !== 'success') {
                return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
            }

            $results = $result['results'];

            DB::beginTransaction();

            // 1. Sinkronisasi Sholat
            if (isset($results['prayer']['data'])) {
                foreach ($results['prayer']['data'] as $p) {
                    $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
                    if ($studentId) {
                        DB::table('prayer_attendances')->updateOrInsert(
                            ['student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name']],
                            [
                                'check_in_time' => $p['check_in_time'],
                                'status' => $p['status'],
                                'latitude' => $p['latitude'],
                                'longitude' => $p['longitude'],
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            // 2. Sinkronisasi Gerbang
            if (isset($results['gate']['data'])) {
                foreach ($results['gate']['data'] as $g) {
                    $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
                    if ($studentId) {
                        DB::table('daily_attendances')->updateOrInsert(
                            ['student_id' => $studentId, 'date' => $g['date']],
                            [
                                'id' => (string) Str::uuid(),
                                'arrival_time' => $g['arrival_time'],
                                'departure_time' => $g['departure_time'],
                                'status' => $g['status'],
                                'recorded_by' => 'Sync System',
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            // 3. Sinkronisasi Pembelajaran
            if (isset($results['learning']['data'])) {
                foreach ($results['learning']['data'] as $l) {
                    $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
                    if ($studentId) {
                        DB::table('attendances')->updateOrInsert(
                            ['student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date']],
                            [
                                'id' => (string) Str::uuid(),
                                'subject_id' => $l['subject_id'],
                                'check_in_time' => $l['check_in_time'],
                                'status' => $l['status'],
                                'recorded_by' => 'Sync System',
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Sinkronisasi data berhasil diselesaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Pull Attendance Error: " . $e->getMessage());
            return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }
    }
}
