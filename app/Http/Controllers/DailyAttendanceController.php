<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Classroom;
use Carbon\Carbon;
use App\Jobs\SendWhatsappJob; // Queue Job untuk WA
use App\Models\AttendanceSetting; // Jangan lupa import model ini
use Illuminate\Support\Facades\DB; // Tambahkan import DB

class DailyAttendanceController extends Controller
{
    /**
     * Halaman Scanner Gerbang (View)
     * Route: GET /daily-attendance
     */
    public function index()
    {
        return view('daily_attendance.scan');
    }

    /**
     * Proses Scan QR Otomatis (AJAX)
     * Logika: Cek Datang -> Cek Pulang -> Kirim WA
     * Route: POST /daily-attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required'
        ]);

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');

        // 2. Cek Data Absensi Hari Ini
        $attendance = DailyAttendance::where('student_id', $student->id)
                        ->where('date', $date)
                        ->first();


        // AMBIL PENGATURAN DARI DATABASE
        // Kita ambil data pertama (karena setting biasanya cuma 1 baris)
        $setting = AttendanceSetting::first();

        // Fallback value jika database setting kosong (safety code)
        $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
        $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';

        // ==========================================================
        // SKENARIO PULANG (DATA SUDAH ADA)
        // ==========================================================
        if ($attendance) {
            // Jika jam pulang sudah terisi, tolak scan
            if ($attendance->departure_time) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Siswa {$student->name} sudah absen pulang hari ini!"
                ]);
            }

            // --- TAMBAHAN FITUR: Validasi Jam Pulang (10:00) ---
            // Membuat objek waktu jam 10:00 hari ini
            //$jamBatasPulang = Carbon::createFromTime(18, 0, 0);

            // Cek apakah waktu sekarang kurang dari jam 10:00
            if (Carbon::now()->lessThan($batasBolehPulang)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Belum waktunya pulang! Absen pulang baru dibuka pukul " .$batasBolehPulang." "
                ]);
            }
            // ------

            // Update Jam Pulang
            $attendance->update(['departure_time' => $time]);

            // Kirim WA Pulang (Antrian)
            $this->sendNotification($student, 'pulang', $time);

            return response()->json([
                'status' => 'success',
                'type' => 'out', // Sinyal ke JS untuk warna Biru
                'message' => 'Hati-hati di jalan! (Absen Pulang)',
                'student' => $student->name
            ]);
        }

        // ==========================================================
        // SKENARIO DATANG (DATA BARU)
        // ==========================================================

        // Logika Terlambat (Batas jam 07:00)
        // $jamMasukSekolah = Carbon::createFromTime(7, 0, 0); Lama
        $jamMasukSekolah = Carbon::createFromTimeString($batasTerlambat); // Ambil dari DB
        $status = Carbon::now()->greaterThan($jamMasukSekolah) ? 'terlambat' : 'hadir';

        DailyAttendance::create([
            'student_id' => $student->id,
            'date' => $date,
            'arrival_time' => $time,
            'status' => $status
        ]);

        // Kirim WA Datang (Antrian)
        $this->sendNotification($student, 'datang', $time, $status);

        return response()->json([
            'status' => 'success',
            'type' => 'in', // Sinyal ke JS untuk warna Hijau
            'message' => 'Selamat Datang! (Absen Masuk)',
            'student' => $student->name . ' (' . strtoupper($status) . ')'
        ]);
    }

    // =============================================================
    // FITUR INPUT MANUAL (ADMIN/GURU PIKET)
    // =============================================================

    /**
     * Halaman Form Manual
     * Route: GET /daily-attendance/manual
     */
    public function create()
    {
        $students = Student::with('classroom')->orderBy('name')->get();
        return view('daily_attendance.create', compact('students'));
    }

    /**
     * Proses Simpan Manual
     * Route: POST /daily-attendance/manual
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required'
        ]);

        // Simpan atau Update data
        DailyAttendance::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'date' => $request->date
            ],
            [
                'arrival_time' => $request->arrival_time,
                'departure_time' => $request->departure_time,
                'status' => $request->status
            ]
        );

        // Kirim notifikasi manual jika diperlukan (Uncomment jika ingin aktif)
        /*
        $student = Student::with('classroom')->find($request->student_id);
        if ($student) {
            $this->sendNotification($student, 'manual', $request->arrival_time ?? '-', $request->status);
        }
        */

        return redirect()->route('daily.index')->with('success', 'Data absensi harian berhasil disimpan secara manual.');
    }

    /**
     * Helper Private: Kirim Pesan WA via Queue
     * PERBAIKAN: Menangani Relasi Null & Inisialisasi Variabel Pesan
     */
    private function sendNotification($student, $type, $time, $status = '')
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $inf_app = $settings['inf_app'] ?? 'Sistem Sekolah'; // Pakai default jika null
        if (empty($student->phone)) return;

        $tgl = date('d-m-Y');

        // Cek apakah relasi classroom ada, jika tidak pakai '-' agar tidak error
        $kelas = $student->classroom ? $student->classroom->name : '-';

        $msg = ''; // Default string kosong

        if ($type == 'datang') {
            $msg = "*LAPORAN KEDATANGAN (GERBANG)*\n\n" .
                   "Yth. Orang Tua/Wali,\n" .
                   "👤 Nama: *{$student->name}*\n" .
                   "🏫 Kelas: {$kelas}\n" .
                   "📅 Waktu: {$time} WIB\n" .
                   "📝 Status: *" . strtoupper($status) . "*\n\n" .
                   "_Absensi_Sekolah_";
        } elseif ($type == 'pulang') {
            $msg = "*LAPORAN KEPULANGAN (GERBANG)*\n\n" .
                   "Yth. Orang Tua/Wali,\n" .
                   "👤 Nama: *{$student->name}*\n" .
                   "🏫 Kelas: {$kelas}\n" .
                   "📅 Waktu: {$time} WIB\n" .
                   "📝 Status: *PULANG SEKOLAH*\n\n" .
                   "_Absensi_Sekolah_";
        } elseif ($type == 'manual') {
            // Format pesan untuk input manual
            $msg = "*LAPORAN PRESENSI (MANUAL)*\n\n" .
                   "👤 Nama: *{$student->name}*\n" .
                   "🏫 Kelas: {$kelas}\n" .
                   "📝 Status: *" . strtoupper($status) . "*\n" .
                   "Ket: Data diinput manual oleh petugas.\n\n" .
                   "_Absensi_Sekolah_";
        }

        // Hanya dispatch job jika pesan berhasil dibuat
        if (!empty($msg)) {
            SendWhatsappJob::dispatch($student->phone, $msg);
        }
    }

    /**
     * Halaman Dashboard Realtime (Monitor)
     * Route: GET /daily-attendance/monitor
     */
    public function monitor()
    {
        return view('daily_attendance.monitor');
    }

     /**
     * Halaman Dashboard Realtime (Monitor)
     * Route: GET /daily-attendance/monitor
     */
    public function monitorKelas()
    {
        // Ambil daftar kelas untuk dropdown filter
        $classrooms = Classroom::orderBy('name')->get();
        return view('daily_attendance.monitor', compact('classrooms'));
    }


    /**
     * API JSON untuk Data Realtime
     * Route: GET /daily-attendance/api/latest
     */
    /**
     * API JSON untuk Data Realtime (Updated)
     */
    public function getRealtimeData(Request $request)
    {
        $today = date('Y-m-d');

        // 1. Query Data Live (Tabel)
        $query = DailyAttendance::with(['student.classroom'])
                        ->where('date', $today);

        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        $attendances = $query->orderBy('updated_at', 'desc')->take(20)->get();

        $data = $attendances->map(function($item) {
            $jam = $item->departure_time ? $item->departure_time : $item->arrival_time;
            $statusLabel = 'DATANG';
            $badgeColor = 'success';

            if ($item->departure_time) {
                $statusLabel = 'PULANG';
                $badgeColor = 'primary';
            } elseif ($item->status == 'terlambat') {
                $statusLabel = 'TERLAMBAT';
                $badgeColor = 'warning';
            }

            return [
                'nis' => $item->student->nis,
                'name' => $item->student->name,
                'class' => $item->student->classroom->name ?? '-',
                'time' => Carbon::parse($jam)->format('H:i:s'),
                'status_label' => $statusLabel,
                'badge_color' => $badgeColor,
            ];
        });

        // 2. Hitung Summary Global
        $summaryQuery = DailyAttendance::where('date', $today);
        if ($request->filled('classroom_id')) {
            $summaryQuery->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }
        $hadirCount = (clone $summaryQuery)->count();
        $pulangCount = (clone $summaryQuery)->whereNotNull('departure_time')->count();


        // 3. [BARU] Hitung Rekapitulasi Per Kelas
        // Kita join tabel agar bisa group by nama kelas
        $rekapKelas = DailyAttendance::join('students', 'daily_attendances.student_id', '=', 'students.id')
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->where('daily_attendances.date', $today)
            ->select(
                'classrooms.name as nama_kelas',
                DB::raw('count(daily_attendances.arrival_time) as total_datang'),
                DB::raw('count(daily_attendances.departure_time) as total_pulang')
            )
            ->groupBy('classrooms.id', 'classrooms.name')
            ->orderBy('classrooms.name', 'asc')
            ->get();

        return response()->json([
            'data' => $data,
            'summary' => [
                'hadir' => $hadirCount,
                'pulang' => $pulangCount
            ],
            'rekap_kelas' => $rekapKelas // Kirim data rekap ke view
        ]);
    }

    /**
     * API JSON untuk Data Realtime
     * Route: GET /daily-attendance/api/latest
     */
    public function getRealtimeDataKelas(Request $request)
    {
        // 1. Query Dasar
        $query = DailyAttendance::with(['student.classroom'])
                        ->where('date', date('Y-m-d'));

        // 2. Filter Berdasarkan Kelas (Jika ada input)
        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // 3. Ambil Data Tabel (20 Terakhir)
        $attendances = $query->orderBy('updated_at', 'desc')
                        ->take(20)
                        ->get();

        // Format data agar mudah dibaca JS
        $data = $attendances->map(function($item) {
            $jam = $item->departure_time ? $item->departure_time : $item->arrival_time;
            $statusLabel = 'DATANG';
            $badgeColor = 'success'; // Hijau

            if ($item->departure_time) {
                $statusLabel = 'PULANG';
                $badgeColor = 'primary'; // Biru
            } elseif ($item->status == 'terlambat') {
                $statusLabel = 'TERLAMBAT';
                $badgeColor = 'warning'; // Kuning
            }

            return [
                'nis' => $item->student->nis,
                'name' => $item->student->name,
                'class' => $item->student->classroom->name ?? '-',
                'time' => Carbon::parse($jam)->format('H:i:s'),
                'status_label' => $statusLabel,
                'badge_color' => $badgeColor,
            ];
        });

        // 4. Hitung Summary (Total Hadir/Pulang) - Juga difilter
        $summaryQuery = DailyAttendance::where('date', date('Y-m-d'));

        if ($request->filled('classroom_id')) {
            $summaryQuery->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // Clone query agar tidak tumpang tindih
        $hadirCount = (clone $summaryQuery)->count();
        $pulangCount = (clone $summaryQuery)->whereNotNull('departure_time')->count();

        return response()->json([
            'data' => $data,
            'summary' => [
                'hadir' => $hadirCount,
                'pulang' => $pulangCount
            ]
        ]);
    }

    /**
     * Menampilkan laporan absensi dengan filter lengkap.
     */
    

    public function laporan(Request $request)
    {
        // 1. Ambil data siswa untuk dropdown filter
        $students = Student::orderBy('name', 'asc')->get();

        // 2. Query Builder Dasar
        // Load relasi 'student' dan 'classroom' agar tidak N+1 Query
        $query = DailyAttendance::with(['student', 'student.classroom']);

        // --- FILTER 1: SPESIFIK SISWA ---
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // --- FILTER 2: PERIODE WAKTU ---
        // Jika tidak ada filter, default ke hari ini
        $filterType = $request->filter_type ?? 'harian';

        switch ($filterType) {
            case 'harian':
                $date = $request->date ?? now()->format('Y-m-d');
                $query->whereDate('created_at', $date);
                break;

            case 'mingguan':
                // Menggunakan rentang tanggal manual (Start Date s/d End Date)
                $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfWeek();
                $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfWeek();

                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;

            case 'bulanan':
                $month = $request->month ?? now()->month;
                $year  = $request->year ?? now()->year;

                $query->whereMonth('created_at', $month)
                      ->whereYear('created_at', $year);
                break;

            case 'semester':
                $year = $request->year ?? now()->year;
                $semester = $request->semester; // 'ganjil' atau 'genap'

                if ($semester == 'ganjil') {
                    // Semester Ganjil: 1 Juli - 31 Desember
                    $start = Carbon::create($year, 7, 1)->startOfDay();
                    $end   = Carbon::create($year, 12, 31)->endOfDay();
                } else {
                    // Semester Genap: 1 Januari - 30 Juni
                    $start = Carbon::create($year, 1, 1)->startOfDay();
                    $end   = Carbon::create($year, 6, 30)->endOfDay();
                }

                $query->whereBetween('created_at', [$start, $end]);
                break;
        }

        // 3. Urutkan data: Terbaru di atas
        $attendances = $query->latest()->get();

        // 4. Hitung Ringkasan (Opsional, untuk header laporan)
        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
        ];

        return view('daily_attendance.reports.laporan', compact('attendances', 'students', 'summary'));
    }


    
    /**
     * FITUR BARU: Simpan Absensi Gerbang Massal (Bantuan Guru)
     * Digunakan jika scanner rusak atau antrian panjang, guru bisa mem-bypass.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
        ]);

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        $date = Carbon::now()->format('Y-m-d');
        $time = Carbon::now()->format('H:i:s');
        $count = 0;

        foreach ($request->student_ids as $studentId) {
            // Cek apakah sudah ada data (untuk menghindari duplikat)
            $exists = DailyAttendance::where('student_id', $studentId)
                        ->where('date', $date)
                        ->exists();

            if (!$exists) {
                DailyAttendance::create([
                    'student_id'   => $studentId,
                    'date'         => $date,
                    'arrival_time' => $time, // Set jam sekarang sebagai jam datang
                    'status'       => $request->status,
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil menambahkan absensi gerbang manual untuk $count siswa.");
    }

    


}
