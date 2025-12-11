<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\DailyAttendance; // Model untuk Absensi Datang/Pulang
use App\Models\Attendance;      // Model untuk Absensi Pembelajaran/Mapel
use App\Models\Classroom;      // Model untuk Absensi Pembelajaran/Mapel
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TranscriptController extends Controller
{
    /**
     * Halaman pencarian siswa untuk dicetak transkripnya.
     */
    public function index()
    {
        // Ambil daftar kelas urut nama
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::with('classroom')->orderBy('name')->get();
        return view('report.transcript_index', compact('students', 'classrooms'));
    }

    /**
     * Menampilkan Transkrip Lengkap Siswa.
     */
    public function show(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'month'      => 'nullable|numeric',
            'year'       => 'nullable|numeric',
        ]);

        $student = Student::with('classroom')->findOrFail($request->student_id);

        // Filter Waktu (Default: Bulan ini)
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;
        $dateObj = Carbon::create($year, $month, 1);

        // --- 1. DATA ABSENSI HARIAN (Datang & Pulang) ---
        // Menggunakan Model: DailyAttendance
        $dailyLogs = DailyAttendance::where('student_id', $student->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'asc')
            ->get();

        // Statistik Harian
        $dailySummary = [
            'hadir' => $dailyLogs->where('status', 'hadir')->count(),
            'terlambat' => $dailyLogs->where('status', 'terlambat')->count(),
            'izin'  => $dailyLogs->where('status', 'izin')->count(),
            'sakit' => $dailyLogs->where('status', 'sakit')->count(),
            'alpa' => $dailyLogs->where('status', 'alpa')->count(),
        ];

        // --- 2. DATA ABSENSI PEMBELAJARAN (Per Mapel) ---
        // Menggunakan Model: Attendance (Diasumsikan tabel ini punya subject_id)
        $lessonSummary = Attendance::select(
                'subjects.name as subject_name',
                DB::raw('count(*) as total_meetings'),
                DB::raw('sum(case when attendances.status = "hadir" then 1 else 0 end) as total_present'),
                DB::raw('sum(case when attendances.status = "terlambat" then 1 else 0 end) as total_late'),
                DB::raw('sum(case when attendances.status = "sakit" then 1 else 0 end) as total_sick'),
                DB::raw('sum(case when attendances.status = "izin" then 1 else 0 end) as total_permission'),
                DB::raw('sum(case when attendances.status = "alpa" then 1 else 0 end) as total_alpha')
            )
            ->join('subjects', 'attendances.subject_id', '=', 'subjects.id')
            ->where('attendances.student_id', $student->id)
            ->whereMonth('attendances.created_at', $month)
            ->whereYear('attendances.created_at', $year)
            ->groupBy('subjects.name')
            ->get();

        return view('report.transcript_print', compact(
            'student',
            'dailyLogs',
            'dailySummary',
            'lessonSummary',
            'dateObj'
        ));
    }

    /**
     * FITUR BARU: Cetak Transkrip Satu Kelas Sekaligus
     */
    public function printByClass(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'month'        => 'nullable|numeric',
            'year'         => 'nullable|numeric',
        ]);

        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;
        $dateObj = Carbon::create($year, $month, 1);

        // 1. Ambil Kelas dan Siswanya
        $classroom = Classroom::with(['students' => function($q) {
            $q->orderBy('name');
        }])->findOrFail($request->classroom_id);

        $transcripts = [];

        // 2. Loop setiap siswa untuk ambil data absensinya
        foreach ($classroom->students as $student) {
            // Re-use logika pengambilan data (saya ekstrak jadi private method biar rapi)
            $transcripts[] = $this->getStudentAttendanceData($student, $month, $year);
        }

        return view('report.transcript_class_print', compact('classroom', 'transcripts', 'dateObj'));
    }

    // --- Helper Private Method ---
    // Digunakan oleh show() dan printByClass() agar codingan tidak berulang
    private function generateTranscriptData($studentId, $month, $year) {
        $student = Student::with('classroom')->findOrFail($studentId);
        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;
        $dateObj = Carbon::create($year, $month, 1);

        $data = $this->getStudentAttendanceData($student, $month, $year);

        return view('reports.transcript_print', array_merge(['dateObj' => $dateObj], $data));
    }

    private function getStudentAttendanceData($student, $month, $year)
    {
        // 1. DATA HARIAN
        $dailyLogs = DailyAttendance::where('student_id', $student->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'asc')
            ->get();

        $dailySummary = [
            'hadir' => $dailyLogs->where('status', 'hadir')->count(),
            'terlambat' => $dailyLogs->where('status', 'terlambat')->count(),
            'izin'  => $dailyLogs->where('status', 'izin')->count(),
            'sakit' => $dailyLogs->where('status', 'sakit')->count(),
            'alpa' => $dailyLogs->where('status', 'alpa')->count(),
        ];

        // 2. DATA PER MAPEL
        $lessonSummary = Attendance::select(
                'subjects.name as subject_name',
                DB::raw('count(*) as total_meetings'),
                DB::raw('sum(case when attendances.status = "hadir" then 1 else 0 end) as total_present'),
                DB::raw('sum(case when attendances.status = "terlambat" then 1 else 0 end) as total_late'),
                DB::raw('sum(case when attendances.status = "sakit" then 1 else 0 end) as total_sick'),
                DB::raw('sum(case when attendances.status = "izin" then 1 else 0 end) as total_permission'),
                DB::raw('sum(case when attendances.status = "alpa" then 1 else 0 end) as total_alpha')
            )
            ->join('subjects', 'attendances.subject_id', '=', 'subjects.id')
            ->where('attendances.student_id', $student->id)
            ->whereMonth('attendances.created_at', $month)
            ->whereYear('attendances.created_at', $year)
            ->groupBy('subjects.name')
            ->get();

        return [
            'student' => $student,
            'dailyLogs' => $dailyLogs,
            'dailySummary' => $dailySummary,
            'lessonSummary' => $lessonSummary
        ];
    }
}
