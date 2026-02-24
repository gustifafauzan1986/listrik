<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahfizRecord;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TahfizController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Ambil data rekap, bisa difilter berdasarkan nama siswa
    //     $query = TahfizRecord::with(['student', 'teacher'])->latest('date');

    //     if ($request->has('search')) {
    //         $query->whereHas('student', function($q) use ($request) {
    //             $q->where('name', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     // Jika yang login siswa, hanya tampilkan miliknya
    //     if (Auth::user()->hasRole('siswa')) {
    //         $query->where('student_id', Auth::id());
    //     }

    //     $records = $query->paginate(15);
        
    //     // Ambil daftar siswa untuk form input (Hanya guru/admin yang butuh)
    //     $students = User::role('siswa')->orderBy('name')->get();
        
    //     // Ambil daftar surah Juz 30 dari Model
    //     $surahs = TahfizRecord::getJuz30Surahs();

    //     return view('siswa.tahfiz.index', compact('records', 'students', 'surahs'));
    // }

public function index(Request $request)
{
    // 1. Ambil data kelas untuk dikirim ke Dropdown
    // (Sesuaikan "Classroom" dengan nama model kelas kamu)
    $classes = \App\Models\Classroom::all(); 

    // 2. Query dasar (beserta relasinya agar tidak N+1 issue)
    $query = TahfizRecord::with(['student', 'teacher']);

    // 3. Filter jika form pencarian nama diisi
    if ($request->filled('search')) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    // 4. Filter jika Dropdown Kelas dipilih
    if ($request->filled('kelas_id')) {
        $query->whereHas('student', function($q) use ($request) {
            // Sesuaikan "classroom_id" dengan nama foreign key di tabel students
            $q->where('classroom_id', $request->kelas_id); 
        });
    }

    // 5. Eksekusi query
    $records = $query->latest('date')->paginate(10)->withQueryString();

        // Ambil daftar siswa untuk form input (Hanya guru/admin yang butuh)
        $students = User::role('siswa')->orderBy('name')->get();
        
        // Ambil daftar surah Juz 30 dari Model
        $surahs = TahfizRecord::getJuz30Surahs();

    // Pastikan $classes ikut dikirim ke view
    return view('siswa.tahfiz.index', compact('records', 'classes', 'students', 'surahs')); 
}

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'surah_name' => 'required|string',
            'ayat' => 'nullable|string',
            'predicate' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        TahfizRecord::create([
            'student_id' => $request->student_id,
            'teacher_id' => Auth::id(), // Guru yang sedang login
            'surah_name' => $request->surah_name,
            'ayat' => $request->ayat ?? 'Lengkap',
            'predicate' => $request->predicate,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('tahfiz.index')->with('success', 'Setoran hafalan berhasil dicatat!');
    }

    public function destroy($id)
    {
        $record = TahfizRecord::findOrFail($id);
        $record->delete();

        return redirect()->route('tahfiz.index')->with('success', 'Data hafalan berhasil dihapus!');
    }
}