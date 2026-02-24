<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahfizRecord;
use App\Models\Classroom; // Sesuaikan jika nama model kelas Anda berbeda
use App\Models\User;
use App\Models\Student;
use Carbon\Carbon;
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

    // public function index(Request $request)
    // {
    //     // Tambahkan baris ini
    //     $today = \Carbon\Carbon::today()->toDateString();

    //     // ... kode kamu yang lain (seperti ambil jadwal, attendances, dll) ...
    //     // 1. Ambil data kelas untuk dikirim ke Dropdown
    //     // (Sesuaikan "Classroom" dengan nama model kelas kamu)
    //     $classes = Classroom::all();

    //     // 2. Query dasar (beserta relasinya agar tidak N+1 issue)
    //     $query = TahfizRecord::with(['student', 'teacher']);

    //     // 3. Filter jika form pencarian nama diisi
    //     // if ($request->filled('search')) {
    //     //     $query->whereHas('student', function($q) use ($request) {
    //     //         $q->where('name', 'like', '%' . $request->search . '%');
    //     //     });
    //     // }
    //     // LOGIKA FILTER NAMA SISWA (Case-Insensitive)
    //     if ($request->filled('search')) {
    //         $query->whereHas('student', function($q) use ($request) {
    //             // 1. Ubah kata kunci pencarian menjadi huruf kecil semua
    //             $searchTerm = strtolower($request->search);

    //             // 2. Gunakan whereRaw dengan fungsi LOWER() dari SQL
    //             $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
    //         });
    //     }

    //     // // 4. Filter jika Dropdown Kelas dipilih
    //     // if ($request->filled('kelas_id')) {
    //     //     $query->whereHas('student', function($q) use ($request) {
    //     //         // Sesuaikan "classroom_id" dengan nama foreign key di tabel students
    //     //         $q->where('classroom_id', $request->kelas_id);
    //     //     });
    //     // }

    //     // 4. Filter jika Dropdown Kelas dipilih
    //     // if ($request->filled('kelas_id')) {
    //     //     $query->whereHas('student', function($q) use ($request) {
    //     //         // Kita tambahkan "users." di depan nama kolom
    //     //         // agar PostgreSQL tidak bingung kolom ini milik siapa
    //     //         $q->where('users.classroom_id', $request->kelas_id);
    //     //     });
    //     // }

    //     // 4. Filter jika Dropdown Kelas dipilih
    //     // 4. Filter Berdasarkan Kelas (Mencari di tabel students)
    //     // if ($request->filled('kelas_id')) {
    //     //     $query->whereHas('student', function($q) use ($request) {
    //     //         // Kita spesifik arahkan ke students.classroom_id
    //     //         $q->where('students.classroom_id', $request->kelas_id);
    //     //     });
    //     // }

    //     // 4. Filter Berdasarkan Kelas (Cara Subquery - Paling Aman untuk PostgreSQL)
    //     if ($request->filled('kelas_id')) {
    //         $query->whereIn('student_id', function($q) use ($request) {
    //             $q->select('id') // ID ini adalah ID di tabel students
    //             ->from('students')
    //             ->where('classroom_id', $request->kelas_id);
    //         });
    //     }

    //     // 5. Eksekusi query
    //     $records = $query->latest('date')->paginate(10)->withQueryString();

    //         // Ambil daftar siswa untuk form input (Hanya guru/admin yang butuh)
    //         // $students = User::role('siswa')->orderBy('name')->get();
    //         // Ambil daftar siswa dari model Student agar ID-nya sinkron
    //         $students = \App\Models\Student::orderBy('name')->get();

    //         // Ambil daftar surah Juz 30 dari Model
    //         $surahs = TahfizRecord::getJuz30Surahs();

    //     // Pastikan $classes ikut dikirim ke view
    //     return view('siswa.tahfiz.index', compact('records', 'classes', 'students', 'surahs', 'today'));
    // }
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();

        // Ambil master data
        $classes = Classroom::orderBy('name', 'asc')->get();
        $students = Student::orderBy('name', 'asc')->get();

        $surahs = [
            'An-Naba', 'An-Naziat', 'Abasa', 'At-Takwir', 'Al-Infitar',
            'Al-Mutaffifin', 'Al-Inshiqaq', 'Al-Buruj', 'At-Tariq', 'Al-A\'la',
            'Al-Ghashiyah', 'Al-Fajr', 'Al-Balad', 'Ash-Shams', 'Al-Lail',
            'Ad-Duha', 'Ash-Sharh', 'At-Tin', 'Al-\'Alaq', 'Al-Qadr',
            'Al-Bayyinah', 'Az-Zalzalah', 'Al-\'Adiyat', 'Al-Qari\'ah', 'At-Takathur',
            'Al-\'Asr', 'Al-Humazah', 'Al-Fil', 'Quraish', 'Al-Ma\'un',
            'Al-Kauthar', 'Al-Kafirun', 'An-Nasr', 'Al-Masad', 'Al-Ikhlas',
            'Al-Falaq', 'An-Nas'
        ];

        // Query Utama dengan Eager Loading tabel 'student'
        $query = TahfizRecord::with(['student', 'teacher']);

        // 1. Filter Pencarian Nama (Case-Insensitive PostgreSQL)
        if ($request->filled('search')) {
            $query->whereHas('student', function($q) use ($request) {
                $searchTerm = strtolower($request->search);
                $q->whereRaw('LOWER(students.name) LIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        // 2. Filter Kelas (Gunakan WhereIn untuk UUID di PostgreSQL)
        if ($request->filled('kelas_id')) {
            $query->whereIn('student_id', function($q) use ($request) {
                $q->select('id')
                  ->from('students')
                  ->where('classroom_id', $request->kelas_id);
            });
        }

        $records = $query->latest('date')->paginate(12)->withQueryString();

        return view('siswa.tahfiz.index', compact('records', 'classes', 'students', 'surahs', 'today'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'surah_name' => 'required|array', // Ubah menjadi array
            'ayat' => 'nullable|string',
            'predicate' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // WAJIB DITAMBAHKAN: Ubah array surah menjadi teks (string) yang dipisah koma
        // sebelum disimpan ke database
        $surahs = implode(', ', $request->surah_name);

        TahfizRecord::create([
            'student_id' => $request->student_id,
            'teacher_id' => Auth::id(), // Guru yang sedang login
            // 'surah_name' => $request->surah_name,
            'surah_name' => $surahs, // Masukkan variabel $surahs yang sudah di-implode
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

// {
//     public function index(Request $request)
//     {
//         $today = Carbon::today()->toDateString();

//         // 1. Ambil data master untuk Dropdown Filter & Form Input
//         $classes = Classroom::orderBy('name', 'asc')->get();

//         // Ambil daftar siswa (disesuaikan dengan role/kebutuhan Anda)
//         $students = User::role('siswa')->orderBy('name', 'asc')->get();

//         // Daftar surah Juz 30
//         $surahs = [
//             'An-Naba', 'An-Naziat', 'Abasa', 'At-Takwir', 'Al-Infitar',
//             'Al-Mutaffifin', 'Al-Inshiqaq', 'Al-Buruj', 'At-Tariq', 'Al-A\'la',
//             'Al-Ghashiyah', 'Al-Fajr', 'Al-Balad', 'Ash-Shams', 'Al-Lail',
//             'Ad-Duha', 'Ash-Sharh', 'At-Tin', 'Al-\'Alaq', 'Al-Qadr',
//             'Al-Bayyinah', 'Az-Zalzalah', 'Al-\'Adiyat', 'Al-Qari\'ah', 'At-Takathur',
//             'Al-\'Asr', 'Al-Humazah', 'Al-Fil', 'Quraish', 'Al-Ma\'un',
//             'Al-Kauthar', 'Al-Kafirun', 'An-Nasr', 'Al-Masad', 'Al-Ikhlas',
//             'Al-Falaq', 'An-Nas'
//         ];

//         // 2. Query dasar dengan Eager Loading untuk performa
//         $query = TahfizRecord::with(['student', 'teacher']);

//         // 3. Filter Pencarian Nama (Case-Insensitive PostgreSQL)
//         if ($request->filled('search')) {
//             $query->whereHas('student', function($q) use ($request) {
//                 $searchTerm = strtolower($request->search);
//                 // Menggunakan users.name untuk menghindari ambiguity di PostgreSQL
//                 $q->whereRaw('LOWER(users.name) LIKE ?', ['%' . $searchTerm . '%']);
//             });
//         }

//         // 4. Filter Berdasarkan Kelas (UUID)
//         if ($request->filled('kelas_id')) {
//             $query->whereHas('student', function($q) use ($request) {
//                 // Spesifik menunjuk ke users.classroom_id sesuai desain tabel Anda
//                 $q->where('users.classroom_id', $request->kelas_id);
//             });
//         }

//         // 5. Eksekusi query dengan Pagination
//         $records = $query->latest('date')->paginate(10)->withQueryString();

//         // 6. Return View
//         return view('siswa.tahfiz.index', compact('records', 'classes', 'students', 'surahs', 'today'));
//     }

//     public function store(Request $request)
//     {
//         // Validasi Input
//         $request->validate([
//             'student_id'   => 'required|exists:users,id',
//             'date'         => 'required|date',
//             'surah_name'   => 'required|array', // Validasi sebagai array karena multi-select
//             'ayat'         => 'nullable|string',
//             'predicate'    => 'required|string',
//             'notes'        => 'nullable|string',
//         ]);

//         try {
//             // Gabungkan array surah menjadi satu string dipisah koma
//             $surahString = implode(', ', $request->surah_name);

//             TahfizRecord::create([
//                 'student_id' => $request->student_id,
//                 'teacher_id' => Auth::id(), // ID Guru yang sedang login
//                 'date'       => $request->date,
//                 'surah_name' => $surahString,
//                 'ayat'       => $request->ayat,
//                 'predicate'  => $request->predicate,
//                 'notes'      => $request->notes,
//             ]);

//             return redirect()->back()->with('success', 'Setoran tahfiz siswa berhasil dicatat!');
//         } catch (\Exception $e) {
//             return redirect()->back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
//         }
//     }

//     public function destroy($id)
//     {
//         try {
//             $record = TahfizRecord::findOrFail($id);
//             $record->delete();

//             return redirect()->back()->with('success', 'Data setoran berhasil dihapus!');
//         } catch (\Exception $e) {
//             return redirect()->back()->withErrors(['error' => 'Gagal menghapus data.']);
//         }
//     }
// }
