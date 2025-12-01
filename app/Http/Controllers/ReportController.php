<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule; // Import Schedule
use App\Models\Classroom; // <--- Import Model Classroom
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Student;
// ... Jangan lupa import Model Setting di paling atas
use App\Models\Setting;

class ReportController extends Controller
{
    public function index()
    {
        // Ambil data kelas untuk Dropdown Filter
        $classrooms = Classroom::orderBy('name')->get();
        // Ambil data siswa untuk Dropdown Pencarian Siswa
        $students = Student::with('classroom')->orderBy('name')->get();

        return view('report.index', compact('classrooms', 'students'));
    }

    public function print(Request $request)
    {
        $startDate = null;
        $endDate = null;
        $labelPeriode = "";

        // LOGIKA PENENTUAN TANGGAL
        switch ($request->periode) {
            case 'harian':
                $startDate = $request->tanggal;
                $endDate = $request->tanggal;
                $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
                break;

            case 'mingguan':
                $request->validate([
                    'start_date' => 'required|date',
                    'end_date'   => 'required|date|after_or_equal:start_date',
                ]);
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
                break;

            case 'bulanan':
                $month = $request->bulan;
                $year = $request->tahun_bulan;

                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

                $labelPeriode = "Bulan " . Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
                break;

            case 'semester':
                $year = $request->tahun_semester;
                if ($request->semester == 'ganjil') {
                    // Ganjil: Juli Tahun Ini - Desember Tahun Ini
                    $startDate = $year . '-07-01';
                    $endDate   = $year . '-12-31';
                    $labelPeriode = "Semester Ganjil T.A $year/" . ($year+1);
                } else {
                    // Genap: Januari Tahun Depan - Juni Tahun Depan
                    $startDate = ($year + 1) . '-01-01';
                    $endDate   = ($year + 1) . '-06-30';
                    $labelPeriode = "Semester Genap T.A $year/" . ($year+1);
                }
                break;
        }

        $school = [
            'name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'address' => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'   => Setting::value('school_phone', '-'),
            'web'     => Setting::value('school_web', '-'),
            'email'   => Setting::value('school_email', '-'),
            // Tambahan Logo
            'logo_left'  => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
        ];

         // 3. QUERY DATA ABSENSI (DENGAN FILTER KELAS)
        $query = Attendance::with(['student', 'schedule'])
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'asc')
                        ->orderBy('check_in_time', 'asc');

        $labelTambahan = null;

        // --- TAMBAHAN: Filter Per Kelas ---
        if ($request->filled('classroom_id')) {
            // Filter hanya siswa yang berada di kelas yang dipilih
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });

            // Ambil nama kelas untuk judul PDF
            $kelas = Classroom::find($request->classroom_id);
            if ($kelas) {
                $labelTambahan = "Kelas: " . $kelas->name;
            }
        }
        // ----------------------------------

        $attendances = $query->get();

        // GENERATE PDF
        $pdf = Pdf::loadView('report.pdf_view', compact(
            'attendances',
            'labelPeriode',
            'startDate',
            'endDate',
            'school'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-Absensi.pdf');
    }

    /**
     * METHOD BARU: Cetak Laporan Spesifik Jadwal/Mapel
     * Diakses dari tombol PDF di halaman Jadwal Mengajar
     */
    public function printSchedule($id)
    {
        // 1. Ambil Data Jadwal
        $schedule = Schedule::with('classroom')->findOrFail($id);

        // 2. Ambil Data Absensi Jadwal Tersebut
        // Kita ambil data semester ini (opsional) atau semua history
        $attendances = Attendance::with(['student', 'schedule'])
                        ->where('schedule_id', $id)
                        ->orderBy('date', 'desc') // Tanggal terbaru di atas
                        ->orderBy('check_in_time', 'desc')
                        ->get();

        // 3. Siapkan Variabel untuk Header PDF
        // Karena view PDF kita butuh variable startDate/endDate, kita ambil dari data pertama & terakhir
        if ($attendances->count() > 0) {
            $startDate = $attendances->last()->date; // Tanggal terlama
            $endDate = $attendances->first()->date;  // Tanggal terbaru
        } else {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }

        $labelPeriode = "Rekapitulasi Mata Pelajaran";
        $labelTambahan = "Mapel: " . $schedule->subject_name . " - Kelas: " . ($schedule->classroom->name ?? '-');


            $school = [
            'name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'address' => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'   => Setting::value('school_phone', '-'),
            'web'     => Setting::value('school_web', '-'),
            'email'   => Setting::value('school_email', '-'),
        ];
        // 4. Generate PDF
        // Kita reuse (gunakan kembali) view 'report.pdf_view' yang sudah dibuat sebelumnya
        $pdf = Pdf::loadView('report.pdf_view', compact(
            'school',
            'attendances',
            'labelPeriode',
            'labelTambahan',
            'startDate',
            'endDate'
        ));

        // 2. LEWATKAN data $school ke view
        // Menggunakan compact() adalah cara yang ringkas

        // Jika Anda menggunakan Dompdf (barryvdh/laravel-dompdf):
        //$pdf = PDF::loadView('report.pdf_view', $data);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-' . $schedule->subject_name . '.pdf');
    }

    /**
     * Print Student Individual History Report (Transcript)
     * Route: /report/student/{id}
     */
    public function printStudent($id)
    {
        // 1. Fetch Student Data
        $student = Student::with('classroom')->findOrFail($id);

        // 2. Fetch Attendance History
        $attendances = Attendance::with(['schedule.subject', 'schedule.teacher'])
                        ->where('student_id', $id)
                        ->orderBy('date', 'desc')
                        ->orderBy('check_in_time', 'desc')
                        ->get();

        // 3. Calculate Statistics
        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
            'total' => $attendances->count()
        ];

        // 4. Determine Date Range for Header
        $startDate = $attendances->last()->date ?? date('Y-m-d');
        $endDate = $attendances->first()->date ?? date('Y-m-d');

        // 5. Get School Settings
        $school = $this->getSchoolData();

        // 6. Generate PDF using specific view
        $pdf = Pdf::loadView('report.student_history', compact(
            'student',
            'attendances',
            'summary',
            'startDate',
            'endDate',
            'school'
        ));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-Siswa-' . $student->name . '.pdf');
    }

    /**
     * Private Helper: Get School Data from Settings
     */
    private function getSchoolData()
    {
        return [
            'name'       => Setting::value('school_name', 'SMK DEFAULT'),
            'address'    => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'      => Setting::value('school_phone', '-'),
            'web'        => Setting::value('school_web', '-'),
            'email'      => Setting::value('school_email', '-'),
            'logo_left'  => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
        ];
    }
}
