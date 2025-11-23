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
    public function index($schedule_id)
    {
        //dd($schedule_id);
        // Cari jadwal berdasarkan ID, pastikan milik guru yang login (Security)
        $schedule = Schedule::where('id', $schedule_id)
                    //->where('teacher_id', Auth::id())
                    ->firstOrFail();

        // Kirim data jadwal ke view scan
        //dd($schedule);;
        return view('scan', compact('schedule'));
    }

    public function store(Request $request)
{
    $request->validate([
        'nis' => 'required',
        'schedule_id' => 'required|exists:schedules,id'
    ]);

    // 1. Cari Siswa
    $student = Student::where('nis', $request->nis)->first();
    if (!$student) {
        return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);
    }

    // 2. Validasi Jadwal (Pastikan jadwal ini milik guru yang sedang login)
    $schedule = Schedule::where('id', $request->schedule_id)
                ->where('teacher_id', Auth::id())
                ->first();

    if (!$schedule) {
        return response()->json(['status' => 'error', 'message' => 'Jadwal tidak valid!']);
    }

    // 3. Cek Duplikasi (Siswa sudah scan di jadwal ID ini?)
    $existing = Attendance::where('student_id', $student->id)
                ->where('schedule_id', $schedule->id) // Cek spesifik ID jadwal
                ->where('date', date('Y-m-d'))
                ->first();

    if ($existing) {
        return response()->json(['status' => 'error', 'message' => 'Siswa sudah absen!']);
    }

    // 4. Simpan
    Attendance::create([
        'student_id' => $student->id,
        'schedule_id' => $schedule->id,
        'date' => date('Y-m-d'),
        'check_in_time' => date('H:i:s'),
        'status' => 'hadir'
    ]);

    return response()->json([
        'status' => 'success', 
        'message' => 'Berhasil', 
        'student' => $student->name
    ]);
}
}