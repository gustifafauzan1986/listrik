<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\DailyAttendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // <--- Import Library QR

class StudentAreaController extends Controller
{
    /**
     * Halaman Profil Siswa (Edit HP & Alamat)
     */
    public function profile()
    {
        // Ambil data siswa yang sedang login
        $students = Student::where('user_id', Auth::id())->with('classroom')->firstOrFail();

        return view('student_area.profile', compact('students'));
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

    /**
     * [BARU] Cetak Kartu Sendiri
     */
    public function printCard()
    {
        $settings = $this->getSchoolData();
        // Ambil data siswa yang sedang login
        $students = Student::where('user_id', Auth::id())->with('classroom')->firstOrFail();
        
        // Generate QR Code berdasarkan NIS
        $qrcode = QrCode::size(120)->generate($students->nis);
        
        // Gunakan view yang SAMA dengan milik Admin agar desain konsisten
        return view('print.single_card', compact('students', 'qrcode', 'settings'));
    }

    private function getSchoolData()
    {
        return [
            // Identitas Sekolah
            'name'       => Setting::value('school_name', 'SMK DEFAULT'),
            'address'    => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'      => Setting::value('school_phone', '-'),
            'web'        => Setting::value('school_web', '-'),
            'email'      => Setting::value('school_email', '-'),
            'logo_left'  => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),

            // Pengaturan Kertas
            'paper_size'        => Setting::value('paper_size', 'a4'),
            'paper_orientation' => Setting::value('paper_orientation', 'portrait'),
            
            // Pengaturan Margin (Tambahkan satuan cm/mm untuk CSS)
            'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
            'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
            'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
            'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',

            // Tanda Tangan
            'sign_city'  => Setting::value('signature_city', 'Jakarta'),
            'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
            'sign_name'  => Setting::value('signature_name', 'Administrator'),
            'sign_nip'   => Setting::value('signature_nip', '-'),
        ];
    }
}
