<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PrayerAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrayerMonitoringController extends Controller
{
    /**
     * Menampilkan daftar absensi sholat seluruh siswa
     */
    public function index(Request $request)
    {
        // Default filter ke hari ini jika tidak ada input tanggal
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        
        // Mengambil data absensi dengan relasi siswa
        // Catatan: Mengambil semua data tanpa limit (sesuai aturan Rule 2 untuk pemrosesan memori)
        $attendances = PrayerAttendance::with('student.user')
            ->whereDate('date', $date)
            ->get()
            ->sortByDesc('check_in_time');

        return view('admin.prayer.monitoring', compact('attendances', 'date'));
    }

    /**
     * Menampilkan detail lokasi siswa di peta (Opsional jika ingin page khusus)
     */
    public function showLocation($id)
    {
        $attendance = PrayerAttendance::with('student.user')->findOrFail($id);
        
        return view('admin.prayer.location', compact('attendance'));
    }
}