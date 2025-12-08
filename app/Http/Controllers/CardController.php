<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Setting;
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
        $settings = Setting::pluck('value', 'key')->toArray();

        // Ambil siswa HANYA dari kelas tersebut
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Kelas ini belum memiliki siswa.');
        }

        // Kita gunakan view 'print.all_cards' yang sudah ada (Reusable)
        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }

    /**
     * Cetak Semua Kartu (Massal Satu Sekolah)
     */
    public function printAll()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $students = Student::with('classroom')->orderBy('classroom_id')->orderBy('name')->get();
        return view('print.all_cards', compact('students', 'settings'));
    }

    /**
     * Cetak Satu Kartu Saja (Perorangan)
     */
    public function printSingle($id)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $student = Student::with('classroom')->findOrFail($id);
        $qrcode = QrCode::size(120)->generate($student->nis);

        return view('print.single_card', compact('student', 'qrcode', 'settings'));
    }


    /**
     * [BARU] Halaman Pilih Siswa (Checkbox)
     */
    public function selectStudents($id)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $classroom = Classroom::findOrFail($id);
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        return view('print.select_students', compact('classroom', 'students', 'settings'));
    }

    /**
     * [BARU] Proses Cetak Siswa Terpilih
     */
    public function printSelected(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);
        $settings = Setting::pluck('value', 'key')->toArray();

        $students = Student::with('classroom')
                    ->whereIn('id', $request->student_ids)
                    ->orderBy('name')
                    ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih.');
        }

        // Ambil kelas dari siswa pertama untuk judul (opsional)
        $classroom = $students->first()->classroom;

        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }
}
