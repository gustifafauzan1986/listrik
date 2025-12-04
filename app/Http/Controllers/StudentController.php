<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;

class StudentController extends Controller
{
    /**
     * Tampilkan Daftar Siswa (dengan Pencarian & Pagination)
     */
    public function index(Request $request)
    {
        $query = Student::with('classroom');

        // Logika Pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%");
            });
        }

        // Filter Kelas (Opsional)
        if ($request->has('classroom_id') && $request->classroom_id != '') {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Ambil data dengan pagination (10 per halaman)
        //$students = $query->orderBy('classroom_id')->orderBy('name')->paginate(10);
        $students = $query->orderBy('classroom_id')->orderBy('name')->get()->all();

        // Ambil daftar kelas untuk filter
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.index', compact('students', 'classrooms'));
    }

    /**
     * Form Edit Siswa
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.edit', compact('student', 'classrooms'));
    }

    /**
     * Update Data Siswa
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'nis' => 'required|unique:students,nis,' . $id, // Unique kecuali punya sendiri
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'phone' => 'nullable|numeric',
        ]);

        $student->update([
            'nis' => $request->nis,
            'name' => $request->name,
            'classroom_id' => $request->classroom_id,
            'phone' => $request->phone,
        ]);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Hapus Siswa
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus!');
    }

    public function removeClassroom($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['classroom_id' => null]); // Set kelas jadi null
        return back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas.");
    }
}
