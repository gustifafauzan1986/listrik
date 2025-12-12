<?php

namespace App\Http\Controllers;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Classroom;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function index()
    {
        // Menampilkan daftar mapping
        $assignments = TeachingAssignment::with(['teacher', 'subject', 'classroom'])
                        ->latest()
                        ->get();
        return view('teaching_assignments.index', compact('assignments'));
    }

    public function create()
    {
        $teachers = Teacher::with('major')->orderBy('name')->get();
        $subjects = Subject::with('major')->orderBy('name')->get();
        // Load kelas dengan major untuk filtering di frontend/backend
        $classrooms = Classroom::with('major')->orderBy('name')->get();

        return view('teaching_assignments.create', compact('teachers', 'subjects', 'classrooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        // --- VALIDASI KESESUAIAN JURUSAN ---
        $teacher = Teacher::find($request->teacher_id);
        $classroom = Classroom::find($request->classroom_id);

        // Jika Guru memiliki Jurusan (Guru Produktif), pastikan Kelasnya sesuai
        if ($teacher->major_id && $classroom->major_id) {
            if ($teacher->major_id != $classroom->major_id) {
                return back()->withErrors(['classroom_id' => 'Guru Jurusan ' . $teacher->major->code . ' tidak boleh mengajar di Kelas Jurusan ' . $classroom->major->code]);
            }
        }

        // Simpan Mapping
        TeachingAssignment::create([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'academic_year' => date('Y') // Default tahun ini
        ]);

        return redirect()->route('teaching_assignments.index')->with('success', 'Jadwal mengajar berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        TeachingAssignment::destroy($id);
        return back()->with('success', 'Jadwal dihapus.');
    }
}
