<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\DailyAttendance; // Menggunakan Absensi Harian (Gerbang) sebagai acuan utama
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // --- 1. COUNT DATA MASTER ---
        $countUser      = User::count();
        $countTeacher   = Teacher::count(); // Atau User::where('jenis_user', 'guru')->count();
        $countPiket     = User::where('jenis_user', 'piket')->count();
        $countStudent   = Student::count();

        // --- 2. COUNT ABSENSI HARI INI ---
        // Kita hitung berdasarkan status di DailyAttendance
        $attendanceToday = DailyAttendance::whereDate('date', $today)->get();

        $countHadir     = $attendanceToday->where('status', 'hadir')->count();
        $countTerlambat = $attendanceToday->where('status', 'terlambat')->count();
        $countSakit     = $attendanceToday->where('status', 'sakit')->count();
        $countIzin      = $attendanceToday->where('status', 'izin')->count();
        $countAlpa      = $attendanceToday->where('status', 'alpa')->count();
        
        // Total yang sudah absen (masuk data) hari ini
        $countPresensi  = $attendanceToday->count(); 

        // --- 3. DATA GRAFIK (Untuk Chart.js) ---
        
        // A. Grafik Lingkaran (Pie Chart) - Komposisi Hari Ini
        $pieData = [$countHadir, $countTerlambat, $countSakit, $countIzin, $countAlpa];

        // B. Grafik Batang (Bar Chart) - Tren 7 Hari Terakhir (Mingguan)
        $barLabels = [];
        $barDataHadir = [];
        $barDataTerlambat = [];
        $barDataAlpa = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $formattedDate = $date->format('Y-m-d');
            $barLabels[] = $date->translatedFormat('D, d M'); // Senin, 12 Des

            // Query ringan per tanggal
            $dailyStats = DailyAttendance::whereDate('date', $formattedDate)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $barDataHadir[] = ($dailyStats['hadir'] ?? 0);
            $barDataTerlambat[] = ($dailyStats['terlambat'] ?? 0);
            $barDataAlpa[] = ($dailyStats['alpa'] ?? 0);
        }

        return view('admin.dashboard', compact(
            'countUser', 'countTeacher', 'countPiket', 'countStudent',
            'countHadir', 'countTerlambat', 'countSakit', 'countIzin', 'countAlpa', 'countPresensi',
            'pieData', 'barLabels', 'barDataHadir', 'barDataTerlambat', 'barDataAlpa'
        ));
    }
}