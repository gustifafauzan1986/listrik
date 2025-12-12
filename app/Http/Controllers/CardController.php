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
        $classrooms = Classroom::withCount('students')->orderBy('name')->get();
        return view('print.select_class', compact('classrooms'));
    }

    /**
     * Cetak Kartu Berdasarkan Kelas Spesifik
     */
    public function printByClass($id)
    {
        // Cek apakah kelas ada
        $classroom = Classroom::find($id);
        if (!$classroom) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $settings = Setting::pluck('value', 'key')->toArray();

        // Ambil siswa
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        // VALIDASI: Cek jika kelas kosong
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak dapat mencetak. Kelas ini belum memiliki data siswa.');
        }

        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }

    /**
     * Cetak Semua Kartu (Massal Satu Sekolah)
     */
    public function printAll()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $students = Student::with('classroom')->orderBy('classroom_id')->orderBy('name')->get();

        // VALIDASI: Cek jika database kosong
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Database siswa masih kosong. Belum ada data untuk dicetak.');
        }

        return view('print.all_cards', compact('students', 'settings'));
    }

    /**
     * Cetak Satu Kartu Saja (Perorangan)
     */
    public function printSingle($id)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        
        // Gunakan find (bukan fail) agar kita bisa redirect cantik dengan SweetAlert
        $student = Student::with('classroom')->find($id);

        if (!$student) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $qrcode = QrCode::size(120)->generate($student->nis);

        return view('print.single_card', compact('student', 'qrcode', 'settings'));
    }

    /**
     * [BARU] Halaman Pilih Siswa (Checkbox)
     */
    public function selectStudents($id)
    {
        $classroom = Classroom::find($id);
        
        if (!$classroom) {
            return redirect()->route('cards.index')->with('error', 'Kelas tidak valid.');
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        if ($students->isEmpty()) {
            return redirect()->route('cards.index')->with('error', 'Kelas ini kosong, tidak ada siswa untuk dipilih.');
        }

        return view('print.select_students', compact('classroom', 'students', 'settings'));
    }

    /**
     * [BARU] Proses Cetak Siswa Terpilih
     */
    public function printSelected(Request $request)
    {
        // VALIDASI INPUT
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ], [
            // Custom Error Message Bahasa Indonesia
            'student_ids.required' => 'Anda belum memilih siswa satupun.',
            'student_ids.exists'   => 'Data siswa yang dipilih tidak valid.'
        ]);

        $settings = Setting::pluck('value', 'key')->toArray();

        $students = Student::with('classroom')
                    ->whereIn('id', $request->student_ids)
                    ->orderBy('name')
                    ->get();

        // Validasi double check (jaga-jaga)
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Gagal memuat data siswa yang dipilih.');
        }

        $classroom = $students->first()->classroom;

        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }
}