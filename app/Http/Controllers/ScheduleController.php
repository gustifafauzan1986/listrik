<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Menampilkan Daftar Jadwal Guru Tersebut
     */
    public function index()
    {
        // Ambil jadwal milik guru yang login saja
        // Urutkan berdasarkan Hari (custom sort) dan Jam
        $schedules = Schedule::with('classroom')
                        ->where('teacher_id', Auth::id())
                        ->get();

        // Sorting Hari (Senin s/d Minggu)
        $daysOrder = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 
            'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7
        ];

        $schedules = $schedules->sortBy(function ($schedule) use ($daysOrder) {
            return $daysOrder[$schedule->day] ?? 99;
        })->sortBy('start_time');

        return view('schedule.index', compact('schedules'));
    }

    /**
     * Form Tambah Jadwal Baru
     */
    public function create()
    {
        // Ambil daftar kelas untuk dropdown
        $classrooms = Classroom::orderBy('name')->get();
        
        return view('schedule.create', compact('classrooms'));
    }

    /**
     * Simpan Jadwal ke Database
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'day'          => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        Schedule::create([
            'teacher_id'   => Auth::id(), // Otomatis ID Guru yang login
            'classroom_id' => $request->classroom_id,
            'subject_name' => $request->subject_name,
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dibuat!');
    }

    /**
     * Hapus Jadwal (Opsional)
     */
    public function destroy($id)
    {
        $schedule = Schedule::where('id', $id)->where('teacher_id', Auth::id())->firstOrFail();
        $schedule->delete();

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dihapus!');
    }
}
