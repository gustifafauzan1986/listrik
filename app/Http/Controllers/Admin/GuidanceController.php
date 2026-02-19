<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ViolationType;
use App\Models\StudentViolation;
use App\Models\StudentGuidance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuidanceController extends Controller
{
    /**
     * Dashboard Pembinaan: List Siswa Bermasalah (Poin Tertinggi)
     */
    // public function index(Request $request)
    // {
    //     // Ambil siswa yang memiliki pelanggaran, urutkan dari poin tertinggi
    //     $students = Student::withSum('violations as total_points', 'violation_types.points')
    //         ->with(['classroom'])
    //         ->having('total_points', '>', 0)
    //         ->orderByDesc('total_points');

    //     return view('admin.guidance.index', compact('students'));
    // }

    /**
     * Dashboard Pembinaan: List Siswa Bermasalah (Poin Tertinggi)
     */
    public function index(Request $request)
    // {
    //     // Query Siswa dengan Total Poin Pelanggaran
    //     $query = Student::query();

    //     // Join ke tabel student_violations dan violation_types untuk menghitung total poin
    //     // Menggunakan subquery agar bisa diurutkan dan difilter
    //     $query->select('students.*')
    //           ->selectSub(function ($q) {
    //               $q->from('student_violations')
    //                 ->join('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
    //                 ->whereColumn('student_violations.student_id', 'students.id')
    //                 ->selectRaw('COALESCE(SUM(violation_types.points), 0)');
    //           }, 'total_points');
        
    //     // Filter Pencarian
    //     if ($request->filled('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('name', 'like', '%' . $request->search . '%')
    //               ->orWhere('nis', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     // Hanya ambil siswa yang punya poin > 0
    //     // Karena 'total_points' adalah alias hasil subquery, kita gunakan having
    //     $query->having('total_points', '>', 0);

    //     // Urutkan dari poin tertinggi
    //     $query->orderByDesc('total_points');

    //     $students = $query->with('classroom')->paginate(15)->withQueryString();

    //     return view('admin.guidance.index', compact('students'));
    // }

        {
        // Query Siswa dengan Total Poin Pelanggaran
        // Menggunakan JOIN dan GROUP BY agar kompatibel dengan PostgreSQL dan HAVING
        $query = Student::query()
            ->leftJoin('student_violations', 'students.id', '=', 'student_violations.student_id')
            ->leftJoin('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
            ->select(
                'students.*',
                DB::raw('COALESCE(SUM(violation_types.points), 0) as total_points')
            )
            ->groupBy('students.id');
        
        // Filter Pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('students.name', 'like', '%' . $request->search . '%')
                  ->orWhere('students.nis', 'like', '%' . $request->search . '%');
            });
        }

        // Hanya ambil siswa yang punya poin > 0
        $query->having(DB::raw('COALESCE(SUM(violation_types.points), 0)'), '>', 0);

        // Urutkan dari poin tertinggi
        $query->orderByDesc('total_points');

        $students = $query->with('classroom')->paginate(15)->withQueryString();

        return view('admin.guidance.index', compact('students'));
    }


    public function create(){
        $students = Student::with('classroom')->orderBy('name')->get();
        // Ambil jenis pelanggaran
        $violationTypes = ViolationType::orderBy('name')->get();
        
        return view('admin.guidance.create', compact('students', 'violationTypes'));
    }

    /**
     * Halaman Detail Siswa: Tampilkan History Pelanggaran & Form Pembinaan
     */
    public function show($id)
    {
        $student = Student::with(['classroom', 'violations.type', 'guidances.teacher'])->findOrFail($id);
        $violationTypes = ViolationType::orderBy('name')->get();
        
        // Cek Role Guru yang sedang login
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        
        $role = 'guru_biasa';
        if($teacher) {
            // Logika sederhana penentuan role (bisa disesuaikan dengan logic role Anda)
            if ($student->classroom->homeroom_teacher_id == $teacher->id) $role = 'wali_kelas';
            // elseif (cek_apakah_guru_bk) $role = 'bk';
            // elseif (cek_apakah_kaprog) $role = 'kaprog';
        }

        return view('admin.guidance.show', compact('student', 'violationTypes', 'role'));
    }

    /**
     * Simpan Pelanggaran Baru
     */
    public function storeViolation(Request $request, $student_id)
    {
        $request->validate([
            'violation_type_id' => 'required|exists:violation_types,id',
            'date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        StudentViolation::create([
            'student_id' => $student_id,
            'violation_type_id' => $request->violation_type_id,
            'date' => $request->date,
            'note' => $request->note,
            'reported_by' => Auth::id()
        ]);

        return back()->with('success', 'Pelanggaran berhasil dicatat.');
    }

    /**
     * Simpan Hasil Pembinaan
     */
    public function storeGuidance(Request $request, $student_id)
    {
        $request->validate([
            'date' => 'required|date',
            'problem_summary' => 'required|string',
            'advice' => 'required|string',
            'student_commitment' => 'required|string',
            'status' => 'required',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        StudentGuidance::create([
            'student_id' => $student_id,
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'problem_summary' => $request->problem_summary,
            'advice' => $request->advice,
            'student_commitment' => $request->student_commitment,
            'status' => $request->status,
            'role_context' => $request->role_context ?? 'guru', // Dikirim dari hidden input view
        ]);

        return back()->with('success', 'Laporan pembinaan berhasil disimpan.');
    }
}