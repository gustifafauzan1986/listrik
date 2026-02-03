<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class PrayerController extends Controller
{
    /**
     * Halaman Jadwal & Absen Sholat
     */
    public function index()
    {
        $user = Auth::user();
        
        // Cek data siswa berdasarkan user login
        $student = Student::where('user_id', $user->id)->first();
        
        // --- FIX ERR_TOO_MANY_REDIRECTS ---
        if (!$student) {
            if (Route::has('dashboard')) {
                return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
            }
            return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
        }

        // 1. Ambil Jadwal Sholat dari API equran.id
        $cityId = '0306'; // Bukittinggi
        $today = Carbon::now()->format('Y-m-d');
        
        $schedule = [];
        try {
            $response = Http::timeout(3)->get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");
            if ($response->successful()) {
                $schedule = $response->json()['data']['jadwal'];
            } else {
                throw new \Exception('Gagal ambil data API');
            }
        } catch (\Exception $e) {
            $schedule = [
                'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40', 
                'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
            ];
        }

        // 2. Cek Status Absensi Hari Ini
        $attendances = PrayerAttendance::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->pluck('check_in_time', 'prayer_name')
                        ->toArray();

        return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    }

    /**
     * Proses Simpan Absen Sholat dengan Validasi GPS Wajib
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'prayer_name' => 'required|in:subuh,dzuhur,ashar,maghrib,isya,dhuha',
            'latitude'    => 'required|numeric', // Diubah menjadi required
            'longitude'   => 'required|numeric', // Diubah menjadi required
        ], [
            'latitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS/Layanan Lokasi Anda aktif.',
            'longitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS/Layanan Lokasi Anda aktif.',
        ]);

        // 2. Security Check: Pastikan koordinat tidak nol atau kosong
        if (empty($request->latitude) || empty($request->longitude)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Absensi gagal! Anda wajib mengaktifkan GPS/Layanan Lokasi pada perangkat Anda.'
            ], 422);
        }

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        $now = Carbon::now();

        // 3. Cek Duplikasi Absen
        $exists = PrayerAttendance::where('student_id', $student->id)
                    ->whereDate('date', $now->format('Y-m-d'))
                    ->where('prayer_name', $request->prayer_name)
                    ->exists();

        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absen untuk sholat ini!'], 400);
        }

        // 4. Simpan Data Absensi
        try {
            PrayerAttendance::create([
                'student_id'    => $student->id,
                'date'          => $now->format('Y-m-d'),
                'prayer_name'   => $request->prayer_name,
                'check_in_time' => $now->format('H:i:s'),
                'status'        => 'hadir',
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'Alhamdulillah, absen sholat berhasil dicatat dengan lokasi GPS.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan sistem saat menyimpan absensi.'
            ], 500);
        }
    }
}