<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Industry;
use App\Models\Internship;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class InternshipStudentController extends Controller
{
    /**
     * Halaman Utama Pemilihan PKL untuk Siswa
     */
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan pada akun ini.');
        }

        // Cek pengajuan PKL siswa saat ini
        $myInternship = Internship::with('industry', 'advisor')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Ambil daftar Industri beserta jumlah kuota yang sudah terisi
        $industries = Industry::withCount(['internships as terisi' => function($q) {
            $q->whereIn('status', ['pending', 'active']);
        }])->orderBy('name')->get();

        return view('siswa.internships.index', compact('industries', 'myInternship'));
    }

    /**
     * Proses Pengajuan Tempat PKL
     */
    public function apply(Request $request)
    {
        $request->validate([
            'industry_id' => 'required|exists:industries,id'
        ]);

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // 1. Cek apakah siswa sudah punya pengajuan yang aktif atau pending
        $hasActiveInternship = Internship::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveInternship) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan PKL yang sedang diproses atau aktif.');
        }

        // 2. Cek Kuota Industri
        $industry = Industry::findOrFail($request->industry_id);
        $terisi = Internship::where('industry_id', $industry->id)
            ->whereIn('status', ['pending', 'active'])
            ->count();

        if ($industry->quota > 0 && $terisi >= $industry->quota) {
            return redirect()->back()->with('error', 'Maaf, kuota untuk tempat PKL ini sudah penuh.');
        }

        // 3. Buat Pengajuan (Status default: pending)
        Internship::create([
            'student_id' => $student->id,
            'industry_id' => $industry->id,
            // Tanggal default bisa diatur oleh admin nanti saat di-approve, 
            // atau diset default untuk semester ini
            'start_date' => now(), 
            'end_date' => now()->addMonths(3), 
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Pengajuan tempat PKL berhasil dikirim! Silakan tunggu persetujuan dari Guru/Admin.');
    }
}
