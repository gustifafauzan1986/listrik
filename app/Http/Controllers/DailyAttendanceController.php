<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use Carbon\Carbon;
use App\Jobs\SendWhatsappJob; // Queue Job untuk WA

class DailyAttendanceController extends Controller
{
    /**
     * Halaman Scanner Gerbang (View)
     * Route: GET /daily-attendance
     */
    public function index()
    {
        return view('daily_attendance.scan');
    }

    /**
     * Proses Scan QR Otomatis (AJAX)
     * Logika: Cek Datang -> Cek Pulang -> Kirim WA
     * Route: POST /daily-attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required'
        ]);

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');

        // 2. Cek Data Absensi Hari Ini
        $attendance = DailyAttendance::where('student_id', $student->id)
                        ->where('date', $date)
                        ->first();

        // ==========================================================
        // SKENARIO PULANG (DATA SUDAH ADA)
        // ==========================================================
        if ($attendance) {
            // Jika jam pulang sudah terisi, tolak scan
            if ($attendance->departure_time) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Siswa {$student->name} sudah absen pulang hari ini!"
                ]);
            }

            // Update Jam Pulang
            $attendance->update(['departure_time' => $time]);

            // Kirim WA Pulang (Antrian)
            $this->sendNotification($student, 'pulang', $time);

            return response()->json([
                'status' => 'success',
                'type' => 'out', // Sinyal ke JS untuk warna Biru
                'message' => 'Hati-hati di jalan! (Absen Pulang)',
                'student' => $student->name
            ]);
        }

        // ==========================================================
        // SKENARIO DATANG (DATA BARU)
        // ==========================================================

        // Logika Terlambat (Batas jam 07:00)
        $jamMasukSekolah = Carbon::createFromTime(7, 0, 0);
        $status = Carbon::now()->greaterThan($jamMasukSekolah) ? 'terlambat' : 'hadir';

        DailyAttendance::create([
            'student_id' => $student->id,
            'date' => $date,
            'arrival_time' => $time,
            'status' => $status
        ]);

        // Kirim WA Datang (Antrian)
        $this->sendNotification($student, 'datang', $time, $status);

        return response()->json([
            'status' => 'success',
            'type' => 'in', // Sinyal ke JS untuk warna Hijau
            'message' => 'Selamat Datang! (Absen Masuk)',
            'student' => $student->name . ' (' . strtoupper($status) . ')'
        ]);
    }

    // =============================================================
    // FITUR INPUT MANUAL (ADMIN/GURU PIKET)
    // =============================================================

    /**
     * Halaman Form Manual
     * Route: GET /daily-attendance/manual
     */
    public function create()
    {
        $students = Student::with('classroom')->orderBy('name')->get();
        return view('daily_attendance.create', compact('students'));
    }

    /**
     * Proses Simpan Manual
     * Route: POST /daily-attendance/manual
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required'
        ]);

        // Simpan atau Update data
        DailyAttendance::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'date' => $request->date
            ],
            [
                'arrival_time' => $request->arrival_time,
                'departure_time' => $request->departure_time,
                'status' => $request->status
            ]
        );

        // Kirim notifikasi manual jika diperlukan (Uncomment jika ingin aktif)
        /*
        $student = Student::with('classroom')->find($request->student_id);
        if ($student) {
            $this->sendNotification($student, 'manual', $request->arrival_time ?? '-', $request->status);
        }
        */

        return redirect()->route('daily.index')->with('success', 'Data absensi harian berhasil disimpan secara manual.');
    }

    /**
     * Helper Private: Kirim Pesan WA via Queue
     * PERBAIKAN: Menangani Relasi Null & Inisialisasi Variabel Pesan
     */
    private function sendNotification($student, $type, $time, $status = '')
    {
        if (empty($student->phone)) return;

        $tgl = date('d-m-Y');

        // Cek apakah relasi classroom ada, jika tidak pakai '-' agar tidak error
        $kelas = $student->classroom ? $student->classroom->name : '-';

        $msg = ''; // Default string kosong

        if ($type == 'datang') {
            $msg = "*LAPORAN KEDATANGAN (GERBANG)*\n\n" .
                   "Yth. Orang Tua,\n" .
                   "Putra/i: *{$student->name}*\n" .
                   "Kelas: {$kelas}\n" .
                   "Waktu: {$time} WIB\n" .
                   "Status: *" . strtoupper($status) . "*\n\n" .
                   "_Sistem Absensi Sekolah_";
        } elseif ($type == 'pulang') {
            $msg = "*LAPORAN KEPULANGAN (GERBANG)*\n\n" .
                   "Yth. Orang Tua,\n" .
                   "Putra/i: *{$student->name}*\n" .
                   "Kelas: {$kelas}\n" .
                   "Waktu: {$time} WIB\n" .
                   "Status: *PULANG SEKOLAH*\n\n" .
                   "_Sistem Absensi Sekolah_";
        } elseif ($type == 'manual') {
            // Format pesan untuk input manual
            $msg = "*LAPORAN PRESENSI (MANUAL)*\n\n" .
                   "Putra/i: *{$student->name}*\n" .
                   "Kelas: {$kelas}\n" .
                   "Status: *" . strtoupper($status) . "*\n" .
                   "Ket: Data diinput manual oleh petugas.\n\n" .
                   "_Sistem Absensi Sekolah_";
        }

        // Hanya dispatch job jika pesan berhasil dibuat
        if (!empty($msg)) {
            SendWhatsappJob::dispatch($student->phone, $msg);
        }
    }
}
