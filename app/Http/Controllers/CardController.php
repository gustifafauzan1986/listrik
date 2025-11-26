<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CardController extends Controller
{
    /**
     * Halaman Pemilihan Kelas (Dashboard Cetak)
     */
    public function index()
    {
        // Ambil semua kelas beserta jumlah siswanya
        $classrooms = Classroom::withCount('students')->orderBy('name')->get();

        return view('print.select_class', compact('classrooms'));
    }

    /**
     * Cetak Kartu Berdasarkan Kelas Spesifik
     */
    public function printByClass($id)
    {
        $classroom = Classroom::findOrFail($id);

        // Ambil siswa HANYA dari kelas tersebut
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Kelas ini belum memiliki siswa.');
        }

        // Kita gunakan view 'print.all_cards' yang sudah ada (Reusable)
        return view('print.all_cards', compact('students', 'classroom'));
    }

    /**
     * Cetak Semua Kartu (Massal Satu Sekolah)
     */
    public function printAll()
    {
        $students = Student::with('classroom')->orderBy('classroom_id')->orderBy('name')->get();
        return view('print.all_cards', compact('students'));
    }

    /**
     * Cetak Satu Kartu Saja (Perorangan)
     */
    public function printSingle($id)
    {
        $student = Student::with('classroom')->findOrFail($id);
        $qrcode = QrCode::size(120)->generate($student->nis);

        return view('print.single_card', compact('student', 'qrcode'));
    }
}
