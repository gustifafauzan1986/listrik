<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\DailyAttendance;
use Illuminate\Support\Facades\Auth;

class StudentAreaController extends Controller
{
    /**
     * Halaman Profil Siswa (Edit HP & Alamat)
     */
    public function profile()
    {
        // Ambil data siswa yang sedang login
        $student = Student::where('user_id', Auth::id())->with('classroom')->firstOrFail();

        return view('student_area.profile', compact('student'));
    }

    /**
     * Proses Update Profil
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'address' => 'nullable|string|max:500',
        ]);

        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $student->update([
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Data profil berhasil diperbarui.');
    }

    /**
     * Riwayat Absensi Mata Pelajaran
     */
    public function historySubject()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $attendances = Attendance::with(['schedule.subject', 'schedule.teacher'])
                        ->where('student_id', $student->id)
                        ->orderBy('date', 'desc')
                        ->orderBy('check_in_time', 'desc')
                        ->paginate(10);

        return view('student_area.history_subject', compact('attendances'));
    }

    /**
     * Riwayat Absensi Harian (Datang/Pulang)
     */
    public function historyDaily()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $dailies = DailyAttendance::where('student_id', $student->id)
                    ->orderBy('date', 'desc')
                    ->paginate(10);

        return view('student_area.history_daily', compact('dailies'));
    }
}
