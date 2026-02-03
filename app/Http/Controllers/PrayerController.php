<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PrayerController extends Controller
{
    /**
     * Halaman Jadwal & Absen Sholat
     */
    public function index()
    {
        $user = Auth::user();
        
        // Asumsi: User yang login terhubung ke Student (Siswa)
        // Jika login sebagai Admin/Guru, mungkin view-nya beda (rekap)
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->back()->with('error', 'Fitur ini khusus untuk akun Siswa.');
        }

        // 1. Ambil Jadwal Sholat dari API equran.id
        // ID Kota Bukittinggi = 0306. Ganti ID ini jika lokasi sekolah berbeda.
        // List ID Kota: https://equran.id/api/v2/shalat/kota/semua
        $cityId = '0306'; 
        $today = Carbon::now()->format('Y-m-d');
        
        $schedule = [];
        try {
            $response = Http::get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");
            if ($response->successful()) {
                $schedule = $response->json()['data']['jadwal'];
            }
        } catch (\Exception $e) {
            // Fallback jika API error/offline (bisa pakai data statis atau db)
            $schedule = [
                'subuh' => '05:00', 'dzuhur' => '12:30', 'ashar' => '15:45', 
                'maghrib' => '18:15', 'isya' => '19:30'
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
     * Proses Simpan Absen Sholat
     */
    public function store(Request $request)
    {
        $request->validate([
            'prayer_name' => 'required|in:subuh,dzuhur,ashar,maghrib,isya,dhuha',
        ]);

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        $now = Carbon::now();

        // Cek Duplikasi
        $exists = PrayerAttendance::where('student_id', $student->id)
                    ->whereDate('date', $now->format('Y-m-d'))
                    ->where('prayer_name', $request->prayer_name)
                    ->exists();

        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah absen sholat ini!'], 400);
        }

        // Logika Waktu (Opsional: Cegah absen Dzuhur di jam Isya, dll)
        // Disini kita bebaskan dulu agar fleksibel

        PrayerAttendance::create([
            'student_id' => $student->id,
            'date' => $now->format('Y-m-d'),
            'prayer_name' => $request->prayer_name,
            'check_in_time' => $now->format('H:i:s'),
            'status' => 'hadir'
        ]);

        return response()->json(['status' => 'success', 'message' => 'Alhamdulillah, absen sholat tercatat.']);
    }
}