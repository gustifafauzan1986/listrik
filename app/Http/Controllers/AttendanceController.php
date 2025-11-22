<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('scan');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required'
        ]);

        // 1. Cari Siswa
        $student = Student::where('nis', $request->nis)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);
        }

        // 2. Cari Jadwal Aktif Guru yang sedang Login
        // Asumsi: Guru sudah login. Kita cari jadwal hari ini, di jam sekarang.
        $now = Carbon::now();
        $dayMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $today = $dayMap[$now->format('l')];

        // Cari jadwal milik guru (Auth::id()) yang harinya sesuai dan jamnya masuk rentang
        $schedule = Schedule::where('teacher_id', Auth::id() ?? 1) // Ganti Auth::id() jika sudah pakai login
                    ->where('day', $today)
                    ->where('start_time', '<=', $now->format('H:i:s'))
                    ->where('end_time', '>=', $now->format('H:i:s'))
                    ->first();

        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada jadwal mengajar aktif saat ini untuk Anda!']);
        }

        // 3. Cek Duplikasi Absensi
        $existing = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('date', $now->format('Y-m-d'))
                    ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'Siswa sudah absen sebelumnya!']);
        }

        // 4. Simpan Absensi
        Attendance::create([
            'student_id' => $student->id,
            'schedule_id' => $schedule->id,
            'date' => $now->format('Y-m-d'),
            'check_in_time' => $now->format('H:i:s'),
            'status' => 'hadir' // Bisa dikembangkan logika terlambat jika > 15 menit
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Absensi Berhasil', 
            'student' => $student->name
        ]);
    }
}