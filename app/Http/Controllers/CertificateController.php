<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    /**
     * Halaman filter untuk memilih periode reward.
     */
    public function index()
    {
        return view('certificates.index');
    }

    /**
     * Proses hitung siswa rajin dan generate sertifikat.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric',
            'year'  => 'required|numeric',
            'limit' => 'required|numeric|min:1|max:10', // Batasi misal Top 3 atau Top 10
            'title' => 'required|string', // Judul sertifikat, misal: "The Most Diligent Student"
        ]);

        $month = $request->month;
        $year = $request->year;
        $limit = $request->limit;

        // LOGIKA: Hitung jumlah 'hadir' terbanyak per siswa
        // Update: Menyesuaikan dengan schema tabel 'daily_attendances'
        $bestStudents = DailyAttendance::with(['student', 'student.classroom'])
            ->select('student_id', DB::raw('count(*) as total_present'))
            // Menggunakan kolom 'date' sesuai schema, bukan created_at
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            // Menggunakan status 'hadir' sesuai enum schema ['hadir', 'terlambat', ...]
            // 'hadir' diasumsikan sebagai datang tepat waktu.
            ->where('status', 'hadir') 
            ->groupBy('student_id')
            ->orderByDesc('total_present') // Urutkan dari jumlah hadir terbanyak
            // Opsional: Jika jumlah hadir sama, bisa diurutkan siapa yang rata-rata arrival_time nya paling awal
            // ->orderBy(DB::raw('AVG(arrival_time)'), 'asc') 
            ->take($limit)
            ->get();

        if ($bestStudents->isEmpty()) {
            return back()->with('error', 'Tidak ada data siswa yang "Hadir" (Tepat Waktu) pada periode tersebut.');
        }

        $period = Carbon::create($year, $month, 1)->translatedFormat('F Y');

        return view('certificates.print', compact('bestStudents', 'period', 'request'));
    }
}