<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\InternshipGrade;
use App\Models\InternshipAttendance; // Import Model Absensi
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class InternshipAssessmentController extends Controller
{
    /**
     * Helper: Ambil Data Guru Login
     */
    private function getTeacher()
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Daftar Siswa Bimbingan PKL
     */
    public function index()
    {
        $teacher = $this->getTeacher();

        // Ambil data PKL dimana guru ini adalah PEMBIMBINGNYA (advisor_id)
        $internships = Internship::with(['student.classroom', 'industry', 'grade'])
            ->where('advisor_id', $teacher->id)
            ->whereIn('status', ['active', 'completed']) // Hanya tampilkan yang aktif/selesai
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('guru.internships.index', compact('internships'));
    }

    // /**
    //  * Form Penilaian Siswa
    //  */
    // public function create($id)
    // {
    //     $teacher = $this->getTeacher();

    //     // Pastikan akses valid (hanya pembimbing sendiri)
    //     $internship = Internship::with(['student', 'industry', 'grade'])
    //         ->where('id', $id)
    //         ->where('advisor_id', $teacher->id)
    //         ->firstOrFail();

    //     return view('guru.internships.assess', compact('internship'));
    // }

    /**
     * Form Penilaian Siswa (UPDATE: Tambah Data Jurnal & Absensi)
     */
    public function create($id)
    {
        $teacher = $this->getTeacher();

        // Pastikan akses valid (hanya pembimbing sendiri)
        $internship = Internship::with(['student.classroom', 'industry', 'grade'])
            ->where('id', $id)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        // 1. Ambil Riwayat Absensi & Jurnal
        $attendances = InternshipAttendance::where('internship_id', $internship->id)
            ->orderBy('date', 'desc')
            ->get();

        // 2. Hitung Statistik Kehadiran
        $summary = [
            'total'   => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'sick'    => $attendances->where('status', 'sick')->count(),
            'permit'  => $attendances->where('status', 'permit')->count(),
            'alpha'   => $attendances->where('status', 'alpha')->count(),
        ];

        return view('guru.internships.assess', compact('internship', 'attendances', 'summary'));
    }

    /**
     * Simpan Nilai
     */
    public function store(Request $request, $id)
    {
        $teacher = $this->getTeacher();
        $internship = Internship::where('id', $id)->where('advisor_id', $teacher->id)->firstOrFail();

        $request->validate([
            'discipline' => 'required|integer|min:0|max:100',
            'teamwork' => 'required|integer|min:0|max:100',
            'initiative' => 'required|integer|min:0|max:100',
            'responsibility' => 'required|integer|min:0|max:100',
            'technical_mastery' => 'required|integer|min:0|max:100',
            'work_quality' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        // Hitung Nilai Akhir (Contoh: Rata-rata sederhana)
        $totalScore = (
            $request->discipline +
            $request->teamwork +
            $request->initiative +
            $request->responsibility +
            $request->technical_mastery +
            $request->work_quality
        ) / 6;

        InternshipGrade::updateOrCreate(
            ['internship_id' => $internship->id],
            [
                'student_id' => $internship->student_id,
                'teacher_id' => $teacher->id,
                'discipline' => $request->discipline,
                'teamwork' => $request->teamwork,
                'initiative' => $request->initiative,
                'responsibility' => $request->responsibility,
                'technical_mastery' => $request->technical_mastery,
                'work_quality' => $request->work_quality,
                'final_score' => round($totalScore, 2),
                'notes' => $request->notes
            ]
        );

        return redirect()->route('teacher.internships.index')
            ->with('success', 'Nilai PKL untuk siswa ' . $internship->student->name . ' berhasil disimpan.');
    }
}
