<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Attendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <--- WAJIB IMPORT HTTP CLIENT
use App\Jobs\SendWhatsappJob; // <--- WAJIB IMPORT JOB

class AttendanceController extends Controller
{
    /**
     * Menampilkan Halaman Scanner
     * URL: /scan/{schedule_id}
     */
    // public function index($schedule_id)
    // {
    //     // 1. Cari Jadwal
    //     // - with('classroom'): Kita butuh nama kelas untuk ditampilkan di judul halaman
    //     // - where('teacher_id', Auth::id()): Keamanan, hanya pemilik jadwal yang bisa buka
    //     $schedule = Schedule::with('classroom')
    //                 ->where('id', $schedule_id)
    //                 ->where('teacher_id', Auth::id())
    //                 ->firstOrFail(); // Jika tidak ketemu/bukan pemiliknya, muncul 404

    //     return view('scan', compact('schedule'));
    // }


    public function index($schedule_id)
    {
        // 1. Ambil data Guru berdasarkan User yang login
        $teacher = Teacher::where('user_id', Auth::id())->first();

        // Validasi jika akun tidak terhubung ke data guru
        if (!$teacher) {
            abort(403, 'Akun Anda tidak terdaftar sebagai Guru.');
        }

        // 2. Cari Jadwal menggunakan ID GURU ($teacher->id), bukan ID USER
        $schedule = Schedule::with('classroom')
                    ->where('id', $schedule_id)
                    ->where('teacher_id', $teacher->id) // PERBAIKAN DI SINI
                    ->firstOrFail();

        return view('scan', compact('schedule'));
    }

    /**
     * Proses Simpan Data Absensi (Dipanggil via AJAX)
     * URL: /scan/store
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required',
    //         'schedule_id' => 'required|exists:schedules,id'
    //     ]);

    //     // 1. Cari Siswa berdasarkan NIS
    //     // Load 'classroom' agar kita tahu nama kelas siswa tersebut (untuk pesan error)
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();

    //     if (!$student) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Data Siswa tidak ditemukan!'
    //         ]);
    //     }

    //     // 2. Cari Jadwal
    //     // Load 'classroom' untuk validasi kesesuaian kelas
    //     $schedule = Schedule::with('classroom')->find($request->schedule_id);

    //     if (!$schedule) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Jadwal tidak valid!'
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA VALIDASI 1: CEK KESESUAIAN KELAS (UUID)
    //     // ---------------------------------------------------------
    //     if ($student->classroom_id !== $schedule->classroom_id) {

    //         $kelasSiswa = $student->classroom->name ?? 'Tanpa Kelas';
    //         $kelasJadwal = $schedule->classroom->name ?? 'Tanpa Kelas';

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => "SALAH KELAS! Siswa {$student->name} terdaftar di {$kelasSiswa}, tidak bisa absen di jadwal {$kelasJadwal}."
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA VALIDASI 2: KEAMANAN KEPEMILIKAN JADWAL
    //     // ---------------------------------------------------------
    //     if ($schedule->teacher_id !== Auth::id()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Security Alert: Anda tidak berhak mengabsen di jadwal guru lain!'
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA VALIDASI 3: CEK DUPLIKASI (SUDAH ABSEN HARI INI?)
    //     // ---------------------------------------------------------
    //     $existing = Attendance::where('student_id', $student->id)
    //                 ->where('schedule_id', $schedule->id)
    //                 ->where('date', date('Y-m-d'))
    //                 ->first();

    //     if ($existing) {
    //         // Pesan berbeda tergantung status sebelumnya
    //         $statusLama = strtoupper($existing->status);
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => "Siswa {$student->name} SUDAH ABSEN sebelumnya! (Status: {$statusLama})"
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA 4: HITUNG KETERLAMBATAN
    //     // ---------------------------------------------------------
    //     $status = 'hadir';

    //     // Pastikan Timezone di config/app.php sudah 'Asia/Jakarta'
    //     $jamMasuk = Carbon::parse($schedule->start_time);
    //     $jamSekarang = Carbon::now();

    //     // Toleransi 15 menit
    //     if ($jamSekarang->greaterThan($jamMasuk->addMinutes(15))) {
    //         $status = 'terlambat';
    //     }

    //     $settings = Setting::pluck('value', 'key')->toArray();

    //     // ---------------------------------------------------------
    //     // 5. SIMPAN KE DATABASE
    //     // ---------------------------------------------------------
    //     Attendance::create([
    //         'student_id' => $student->id,
    //         'schedule_id' => $schedule->id,
    //         'date' => date('Y-m-d'),
    //         'check_in_time' => date('H:i:s'),
    //         'status' => $status
    //     ]);

    //     // ======================================================
    //     // DISPATCH JOB ANTRIAN WHATSAPP (INSTANT RESPONSE)
    //     // ======================================================
    //     if (!empty($student->phone)) {
    //         // Susun Pesan
    //         $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
    //         $waktu = date('H:i');
    //         $tgl = date('d-m-Y');
    //         $statText = strtoupper($status);
    //         $emoji = $status == 'hadir' ? '✅' : '⚠️';

    //         $message = "*LAPORAN KEHADIRAN SISWA*\n\n" .
    //                    "Yth. Orang Tua/Wali,\n" .
    //                    "👤 Putra/Putri Anda: *{$student->name}*\n" .
    //                    "🏫 Kelas: {$student->classroom->name}\n" .
    //                    "📚 Mapel: {$mapel}\n" .
    //                    "📅 Waktu: {$waktu} WIB ($tgl)\n" .
    //                    "📝 Status: *{$statText}* {$emoji}\n\n" .
    //                    "_Sistem Absensi Sekolah._";

    //         // Masukkan ke Antrian (Tidak menunggu WA terkirim)
    //         SendWhatsappJob::dispatch($student->phone, $message);
    //     }
    //     // ======================================================

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Absensi Berhasil (Notifikasi diproses)',
    //         'student' => $student->name . " (" . strtoupper($status) . ")"
    //     ]);
    // }

    public function store(Request $request)
    {
        // ... (Validasi input tetap sama)
        $request->validate([
            'nis' => 'required',
            'schedule_id' => 'required|exists:schedules,id'
        ]);

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Data Siswa tidak ditemukan!']);
        }

        // 2. Cari Jadwal
        $schedule = Schedule::with('classroom')->find($request->schedule_id);
        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => 'Jadwal tidak valid!']);
        }

        // ... (Validasi Kelas & Guru tetap sama)
        if ($student->classroom_id !== $schedule->classroom_id) {
            // ... (kode error kelas mismatch)
            return response()->json(['status' => 'error', 'message' => 'Salah Kelas!']);
        }

        if ($schedule->teacher_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Security Alert!']);
        }

        // ---------------------------------------------------------
        // PERUBAHAN 1: HITUNG STATUS DULUAN
        // Kita harus tahu status 'baru' ini apa sebelum cek DB
        // ---------------------------------------------------------
        $jamMasuk = Carbon::parse($schedule->start_time);
        $jamSekarang = Carbon::now();
        
        // Default status
        $statusBaru = 'hadir';

        // Toleransi 15 menit
        if ($jamSekarang->greaterThan($jamMasuk->addMinutes(15))) {
            $statusBaru = 'terlambat';
        }

        // ---------------------------------------------------------
        // PERUBAHAN 2: LOGIKA PENYIMPANAN CERDAS
        // ---------------------------------------------------------
        $existing = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('date', date('Y-m-d'))
                    ->first();

        $isDataSaved = false; // Flag untuk trigger notifikasi

        if ($existing) {
            // SKENARIO A: DATA SUDAH ADA
            
            // Cek apakah statusnya sama persis?
            if ($existing->status === $statusBaru) {
                // JIKA SAMA: Jangan simpan, Jangan kirim notif, tapi beri respon sukses agar frontend tidak error
                return response()->json([
                    'status' => 'success', // Tetap success agar alat scan tidak bunyi error
                    'message' => 'Siswa sudah absen (Data Sama, Tidak Disimpan)',
                    'student' => $student->name . " (SUDAH ADA)"
                ]);
            } else {
                // JIKA BEDA: Update data (Misal dari 'alpha' jadi 'hadir' atau koreksi status)
                $existing->update([
                    'check_in_time' => date('H:i:s'),
                    'status' => $statusBaru
                ]);
                $isDataSaved = true; // Trigger notifikasi
            }

        } else {
            // SKENARIO B: DATA BELUM ADA (BARU)
            Attendance::create([
                'student_id' => $student->id,
                'schedule_id' => $schedule->id,
                'date' => date('Y-m-d'),
                'check_in_time' => date('H:i:s'),
                'status' => $statusBaru
            ]);
            $isDataSaved = true; // Trigger notifikasi
        }

        // ---------------------------------------------------------
        // KIRIM NOTIFIKASI HANYA JIKA ADA DATA BARU/UPDATE
        // ---------------------------------------------------------
        if ($isDataSaved && !empty($student->phone)) {
            // Susun Pesan
            $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
            $waktu = date('H:i');
            $tgl = date('d-m-Y');
            $statText = strtoupper($statusBaru);
            $emoji = $statusBaru == 'hadir' ? '✅' : '⚠️';

            $message = "*LAPORAN KEHADIRAN SISWA*\n\n" .
                    "Yth. Orang Tua/Wali,\n" .
                    "👤 Putra/Putri Anda: *{$student->name}*\n" .
                    "🏫 Kelas: {$student->classroom->name}\n" .
                    "📚 Mapel: {$mapel}\n" .
                    "📅 Waktu: {$waktu} WIB ($tgl)\n" .
                    "📝 Status: *{$statText}* {$emoji}\n\n" .
                    "_Sistem Absensi Sekolah._";

            SendWhatsappJob::dispatch($student->phone, $message);
        }

        return response()->json([
            'status' => 'success',
            'message' => $existing ? 'Data Absensi Diperbarui' : 'Absensi Berhasil Disimpan',
            'student' => $student->name . " (" . strtoupper($statusBaru) . ")"
        ]);
    }

    // =============================================================
    // FITUR BARU: ABSENSI MANUAL GURU (SAKIT/IZIN/ALPA)
    // =============================================================

    /**
     * Halaman Form Manual (Menampilkan Daftar Siswa)
     * Route: GET /schedule/{id}/manual
     */
    public function createManual($schedule_id)
    {

         $teacher = Teacher::where('user_id', Auth::id())->first();

        // Validasi jika akun tidak terhubung ke data guru
        if (!$teacher) {
            abort(403, 'Akun Anda tidak terdaftar sebagai Guru.');
        }


        // 1. Cari Jadwal & Validasi Pemilik
        $schedule = Schedule::with(['classroom', 'subject'])
                    ->where('id', $schedule_id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        // 2. Ambil semua siswa di kelas tersebut
        $students = Student::where('classroom_id', $schedule->classroom_id)
                    ->orderBy('name')
                    ->get();

        // 3. Ambil data absensi yang SUDAH ada hari ini (untuk mengisi status radio button)
        // Format Array: [student_id => status] contoh: [1 => 'hadir', 2 => 'sakit']
        $existingAttendances = Attendance::where('schedule_id', $schedule_id)
                                ->where('date', date('Y-m-d'))
                                ->pluck('status', 'student_id')
                                ->toArray();

        return view('attendance.manual', compact('schedule', 'students', 'existingAttendances'));
    }

    /**
     * Proses Simpan Absensi Manual (Massal)
     * Route: POST /schedule/{id}/manual
     */

    /**
     * Proses Simpan Absensi Manual (Massal) + Kirim WA Antrian
     */
    // public function storeManual(Request $request, $schedule_id)
    // {
    //     $request->validate([
    //         'attendances' => 'required|array', // Key: student_id, Value: status
    //     ]);

    //     $settings = Setting::pluck('value', 'key')->toArray();
    //     $inf_app = $settings['inf_app'];
    //     //dd($inf_app);

    //     $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

    //     if ($schedule->teacher_id !== Auth::id()) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $date = date('Y-m-d');
    //     $now = date('H:i:s');

    //     // Loop setiap input dari guru
    //     foreach ($request->attendances as $studentId => $status) {
    //         // Jika status tidak dipilih (kosong), lewati
    //         if (!$status) continue;

    //         // 1. Simpan ke Database
    //         Attendance::updateOrCreate(
    //             [
    //                 'student_id'  => $studentId,
    //                 'schedule_id' => $schedule_id,
    //                 'date'        => $date
    //             ],
    //             [
    //                 'check_in_time' => $now,
    //                 'status'        => $status
    //             ]
    //         );

    //         // ==========================================================
    //         // 2. LOGIKA KIRIM WA (ANTRIAN / QUEUE)
    //         // ==========================================================
    //         // Ambil data siswa untuk dapat nomor HP & Nama
    //         $student = Student::find($studentId);

    //         if ($student && !empty($student->phone)) {

    //             // Tentukan Emoji & Pesan berdasarkan status
    //             $emoji = '✅';
    //             $keterangan = 'Hadir di sekolah';

    //             switch ($status) {
    //                 case 'izin':
    //                     $emoji = '📩';
    //                     $keterangan = 'Izin (Diketahui Guru)';
    //                     break;
    //                 case 'sakit':
    //                     $emoji = '💊';
    //                     $keterangan = 'Sakit (Diketahui Guru)';
    //                     break;
    //                 case 'alpa':
    //                     $emoji = '❌';
    //                     $keterangan = 'ALPA / Tanpa Keterangan';
    //                     break;
    //                 case 'terlambat':
    //                     $emoji = '⚠️';
    //                     $keterangan = 'Hadir (Terlambat)';
    //                     break;
    //             }

    //             // Susun Pesan
    //             $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
    //             $tgl = date('d-m-Y');

    //             $message = "*LAPORAN PRESENSI MANUAL*\n\n" .
    //                        "Yth. Orang Tua/Wali,\n" .
    //                        "Informasi kehadiran putra/putri Anda:\n\n" .
    //                        "👤 Nama : *$student->name*\n" .
    //                        "🏫 Kelas : {$schedule->classroom->name}\n" .
    //                        "📚 Mapel : $mapel\n" .
    //                        "📅 Tanggal : $tgl\n" .
    //                        "📝 Status : *" . strtoupper($status) . "* $emoji\n" .
    //                        "ℹ️ Ket : $keterangan\n\n" .
    //                        "_$inf_app, Data ini diinput manual oleh Guru Pengampu._";

    //             // Masukkan ke Antrian (Background Job)
    //             // Ini penting agar proses simpan tidak lemot
    //             SendWhatsappJob::dispatch($student->phone, $message);
    //         }
    //         // ==========================================================
    //     }

    //     return redirect()->route('schedule.index')->with('success', 'Absensi manual disimpan & notifikasi WA sedang dikirim!');
    // }

    public function storeManual(Request $request, $schedule_id)
{
    $request->validate([
        'attendances' => 'required|array', // Key: student_id, Value: status
    ]);

    $settings = Setting::pluck('value', 'key')->toArray();
    $inf_app = $settings['inf_app'] ?? 'Sistem Sekolah'; // Pakai default jika null

    $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

    if ($schedule->teacher_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    $date = date('Y-m-d');
    $now = date('H:i:s');

    $updatedCount = 0; // Opsional: untuk menghitung berapa siswa yang diupdate

    // Loop setiap input dari guru
    foreach ($request->attendances as $studentId => $status) {
        // Jika status tidak dipilih (kosong), lewati
        if (!$status) continue;

        // ---------------------------------------------------------
        // 1. CEK DATA LAMA (EXISTING)
        // ---------------------------------------------------------
        $attendance = Attendance::where('student_id', $studentId)
            ->where('schedule_id', $schedule_id)
            ->where('date', $date)
            ->first();

        $shouldSendNotif = false; // Flag penanda kirim WA

        if ($attendance) {
            // SKENARIO A: DATA SUDAH ADA
            
            // Cek apakah statusnya sama persis?
            if ($attendance->status === $status) {
                // JIKA SAMA: Lewati loop ini. Jangan simpan, jangan kirim WA.
                continue; 
            }

            // JIKA BEDA: Update datanya
            $attendance->update([
                'check_in_time' => $now,
                'status'        => $status
            ]);
            $shouldSendNotif = true; // Status berubah, perlu kirim WA

        } else {
            // SKENARIO B: DATA BELUM ADA (BARU)
            Attendance::create([
                'student_id'  => $studentId,
                'schedule_id' => $schedule_id,
                'date'        => $date,
                'check_in_time' => $now,
                'status'      => $status
            ]);
            $shouldSendNotif = true; // Data baru, perlu kirim WA
        }

        // ==========================================================
        // 2. LOGIKA KIRIM WA (HANYA JIKA ADA PERUBAHAN)
        // ==========================================================
        if ($shouldSendNotif) {
            $updatedCount++; // Counter update bertambah

            // Ambil data siswa HANYA jika notif akan dikirim (Hemat Query)
            $student = Student::find($studentId);

            if ($student && !empty($student->phone)) {

                // Tentukan Emoji & Pesan berdasarkan status
                $emoji = '✅';
                $keterangan = 'Hadir di sekolah';

                switch ($status) {
                    case 'izin':
                        $emoji = '📩';
                        $keterangan = 'Izin (Diketahui Guru)';
                        break;
                    case 'sakit':
                        $emoji = '💊';
                        $keterangan = 'Sakit (Diketahui Guru)';
                        break;
                    case 'alpa':
                        $emoji = '❌';
                        $keterangan = 'ALPA / Tanpa Keterangan';
                        break;
                    case 'terlambat':
                        $emoji = '⚠️';
                        $keterangan = 'Hadir (Terlambat)';
                        break;
                }

                // Susun Pesan
                $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
                $tgl = date('d-m-Y');

                $message = "*LAPORAN PRESENSI MANUAL*\n\n" .
                            "Yth. Orang Tua/Wali,\n" .
                            "Informasi kehadiran putra/putri Anda:\n\n" .
                            "👤 Nama : *$student->name*\n" .
                            "🏫 Kelas : {$schedule->classroom->name}\n" .
                            "📚 Mapel : $mapel\n" .
                            "📅 Tanggal : $tgl\n" .
                            "📝 Status : *" . strtoupper($status) . "* $emoji\n" .
                            "ℹ️ Ket : $keterangan\n\n" .
                            "_$inf_app, Data ini diinput manual oleh Guru Pengampu._";

                // Masukkan ke Antrian (Background Job)
                SendWhatsappJob::dispatch($student->phone, $message);
            }
        }
        // ==========================================================
    }

    // Pesan feedback disesuaikan
    if ($updatedCount > 0) {
        return redirect()->route('schedule.index')->with('success', "Berhasil menyimpan $updatedCount data siswa & notifikasi diproses.");
    } else {
        return redirect()->route('schedule.index')->with('warning', 'Tidak ada perubahan data absensi.');
    }
}
}
