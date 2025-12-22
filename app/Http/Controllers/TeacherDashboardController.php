<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Models\TeachingAssignment;

class TeacherDashboardController extends Controller
{
    /**
     * Menampilkan Dashboard Khusus Guru.
     * Hanya menampilkan Kelas & Mapel yang ditugaskan ke guru yang sedang login.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Cari Data Guru berdasarkan User ID yang login
        $teacher = Teacher::where('user_id', $user->id)->first();

        // Jika user login bukan guru (atau belum ditautkan datanya)
        if (!$teacher) {
            return view('teacher_dashboard.empty', ['message' => 'Akun Anda belum terdaftar sebagai Guru. Hubungi Admin.']);
        }

        // 2. Ambil Jadwal Mengajar (Mapping) milik guru ini
        // Eager load: classroom (untuk nama kelas) dan subject (untuk nama mapel)
        $assignments = TeachingAssignment::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->orderBy('academic_year', 'desc')
                        ->get();

        return view('teacher_dashboard.index', compact('teacher', 'assignments'));
    }
}