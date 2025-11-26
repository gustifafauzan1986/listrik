<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class FaceController extends Controller
{
    /**
     * Halaman Utama: Pilih Kelas / Siswa untuk Registrasi Wajah
     */
    public function index(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();
        $students = [];

        if ($request->has('classroom_id')) {
            $students = Student::where('classroom_id', $request->classroom_id)
                        ->orderBy('name')
                        ->get();
        }

        return view('face.index', compact('classrooms', 'students'));
    }

    /**
     * Halaman Ambil Foto Wajah (Webcam)
     */
    public function register($id)
    {
        $student = Student::with('classroom')->findOrFail($id);
        return view('face.register', compact('student'));
    }

    /**
     * Simpan Descriptor Wajah (AJAX)
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'descriptor' => 'required' // JSON String dari Face API
        ]);

        $student = Student::findOrFail($id);
        $student->update([
            'face_descriptor' => $request->descriptor
        ]);

        return response()->json(['status' => 'success', 'message' => 'Wajah berhasil didaftarkan!']);
    }

    /**
     * API: Ambil Data Wajah Satu Kelas (Untuk Scanner)
     * Dipanggil saat Guru membuka halaman Scan Wajah
     */
    public function getDescriptors($schedule_id)
    {
        // Cari jadwal
        $schedule = Schedule::findOrFail($schedule_id);

        // Ambil siswa di kelas tersebut yang SUDAH punya data wajah
        $students = Student::where('classroom_id', $schedule->classroom_id)
                    ->whereNotNull('face_descriptor')
                    ->select('nis', 'name', 'face_descriptor')
                    ->get();

        // Format data untuk Face API
        $labeledDescriptors = [];
        foreach($students as $student) {
            $labeledDescriptors[] = [
                'label' => $student->nis . ' - ' . $student->name, // Label yang muncul saat terdeteksi
                'descriptor' => json_decode($student->face_descriptor)
            ];
        }

        return response()->json($labeledDescriptors);
    }

    /**
     * Halaman Scanner Khusus Wajah
     */
    public function scan($schedule_id)
    {
        $schedule = Schedule::with('classroom')
                    ->where('id', $schedule_id)
                    ->where('teacher_id', Auth::id())
                    ->firstOrFail();

        return view('face.scan', compact('schedule'));
    }
}
