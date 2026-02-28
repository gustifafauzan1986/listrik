<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kegiatan; // Ganti sesuai nama model Anda
use App\Models\Absensi;  // Ganti sesuai nama model Anda
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk debugging
use Illuminate\Support\Facades\Auth;

class AbsensiKegiatanController extends Controller
{
    public function index(){
        // Mengambil semua data kegiatan, diurutkan dari yang terbaru
        $kegiatans = Kegiatan::latest()->get();

        return view('kegiatan.index', compact('kegiatans'));
    }
    /**
     * Menampilkan Detail Kegiatan dan QR Code
     */
    public function showKegiatan($id)
    {
        // Mencari data kegiatan (Asumsi tabel: id, nama_kegiatan, tanggal, kode_unik)
        $kegiatan = Kegiatan::findOrFail($id);
        $user = auth()->user();
        // LOGIKA REDIRECT:
        // Jika user adalah 'siswa' atau 'guru', alihkan ke halaman scan
        // Sesuaikan 'siswa' dan 'guru' dengan value di database Bapak
        if ($user->jenis_user == 'siswa' || $user->jenis_user == 'guru') {
            return redirect()->route('kegiatan.scan.camera', $kegiatan->kode_unik)
                            ->with('info', 'Anda diarahkan otomatis ke halaman scan absensi.');
        }

        // Jika user adalah Admin / Kepala Bengkel, tampilkan barcode
        $scanUrl = route('kegiatan.scan', $kegiatan->kode_unik);
        return view('kegiatan.show', compact('kegiatan', 'scanUrl'));
        

        // // Generate URL untuk di-scan. Menggunakan kode unik kegiatan.
        // $scanUrl = route('kegiatan.scan', ['kode_unik' => $kegiatan->kode_unik]);

        // // Generate QR Code dari URL tersebut
        // // QrCode::size(300)->generate($scanUrl) akan dicetak di view
        
        // return view('kegiatan.show', compact('kegiatan', 'scanUrl'));
    }

    /**
     * Mencetak Daftar Hadir beserta Tanda Tangan
     */
    public function printKegiatan($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        // Ambil data absensi beserta relasi user dan profil (guru/siswa) untuk mengambil tanda tangan
        $absensi = Absensi::with(['user.teacher', 'user.student'])
                          ->where('kegiatan_id', $id)
                          ->orderBy('created_at', 'asc')
                          ->get();

        return view('kegiatan.print', compact('kegiatan', 'absensi'));
    }

    /**
     * Logika ketika QR Code di-scan
     */
    public function scanQr($kode_unik)
    {
        $kegiatan = Kegiatan::where('kode_unik', $kode_unik)->firstOrFail();
        $user = auth()->user();

        // Cek apakah user sudah absen
        $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
                             ->where('user_id', $user->id)
                             ->exists();

        if (!$sudahAbsen) {
            Absensi::create([
                'kegiatan_id' => $kegiatan->id,
                'user_id' => $user->id,
                'waktu_hadir' => now(),
            ]);
            return redirect()->route('kegiatan.show', $kegiatan->id)
                             ->with('success', 'Berhasil melakukan absensi!');
        }

        return redirect()->route('kegiatan.show', $kegiatan->id)
                         ->with('error', 'Anda sudah melakukan absensi sebelumnya.');
    }

    public function storeKegiatan(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'deskripsi'     => 'nullable|string',
        ]);

        // Generate kode unik acak untuk QR Code (contoh: KEG-A1B2C3D4)
        $kodeUnik = 'KEG-' . strtoupper(Str::random(8));

        // Simpan ke database
        Kegiatan::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal'       => $request->tanggal,
            'kode_unik'     => $kodeUnik,
            'deskripsi'     => $request->deskripsi,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'radius'        => $request->radius,
        ]);

        return redirect()->back()->with('success', 'Kegiatan berhasil ditambahkan!');
    }

    public function scan()
    {
        return view('kegiatan.scan');
    }

     /**
     * Memproses hasil scan QR Code (Ajax/Post).
     */
    // public function proses(Request $request)
    // {
    //     // 1. Validasi input dari QR Code (berupa teks/string 'kode_unik')
    //     $request->validate([
    //         'kode_unik' => 'required|string',
    //     ]);

    //     $kodeQr = $request->kode_unik;

    //     // 2. Cari kegiatan berdasarkan kode unik tersebut
    //     // Pastikan kegiatan aktif/ada di database
    //     $kegiatan = Kegiatan::where('kode_unik', $kodeQr)->first();

    //     // Jika QR Code tidak valid (kegiatan tidak ditemukan)
    //     if (!$kegiatan) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'QR Code tidak valid atau kegiatan tidak ditemukan.'
    //         ], 404);
    //     }

    //     // 3. Cek apakah user (guru) sudah pernah absen di kegiatan ini
    //     // Kita asumsikan user sudah login (menggunakan Auth::id())
    //     $userId = Auth::id(); // Ganti dengan ID user dummy jika belum pasang sistem login
        
    //     // Contoh jika belum ada sistem login, gunakan user id = 1 untuk testing:
    //     // $userId = 1; 

    //     $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
    //                          ->where('user_id', $userId)
    //                          ->exists();

    //     if ($sudahAbsen) {
    //         return response()->json([
    //             'status'  => 'warning',
    //             'message' => 'Anda sudah melakukan absensi untuk kegiatan: ' . $kegiatan->nama_kegiatan
    //         ], 200);
    //     }

    //     // 4. Jika valid dan belum absen, simpan data ke tabel absensis
    //     Absensi::create([
    //         'kegiatan_id' => $kegiatan->id,
    //         'user_id'     => $userId,
    //         'waktu_hadir' => now(), // Waktu saat ini
    //     ]);

    //     // 5. Kembalikan response sukses ke halaman scan (scanner HTML5)
    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Berhasil absen untuk kegiatan: ' . $kegiatan->nama_kegiatan
    //     ], 200);
    // }

    public function proses(Request $request)
    {
        $request->validate([
            'kode_unik' => 'required|string',
        ]);

        // 1. Bersihkan spasi di awal/akhir dan pastikan huruf besar
        // Jika QR Code Anda formatnya berbeda, sesuaikan di sini.
        $kodeQrRaw = $request->kode_unik;
        $kodeQrBersih = trim(strtoupper($kodeQrRaw));

        // --- DEBUGGING (PENTING) ---
        // Buka file storage/logs/laravel.log untuk melihat apa yang sebenarnya dikirim oleh scanner
        Log::info('Menerima hasil scan:', [
            'raw_data' => $kodeQrRaw,
            'cleaned_data' => $kodeQrBersih
        ]);
        // ---------------------------

        // 2. Jika QR Code ternyata berisi URL (misal: https://web.com/scan/KEG-123)
        // Kita ekstrak bagian terakhir (kode-nya saja)
        if (filter_var($kodeQrBersih, FILTER_VALIDATE_URL)) {
            // Ambil segmen terakhir dari URL
            $segments = explode('/', parse_url($kodeQrBersih, PHP_URL_PATH));
            $kodeQrBersih = end($segments);
            Log::info('Ekstrak dari URL menjadi:', ['kode_akhir' => $kodeQrBersih]);
        }

        // 3. Cari kegiatan berdasarkan kode unik yang sudah dibersihkan
        $kegiatan = Kegiatan::where('kode_unik', $kodeQrBersih)->first();

        if (!$kegiatan) {
            // Jika tetap tidak ketemu, kirim balik kode yang dicari agar Anda tahu salahnya di mana
            return response()->json([
                'status'  => 'error',
                'message' => "Kegiatan tidak ditemukan. Kode yang dicari: [{$kodeQrBersih}]"
            ], 404);
        }

        // 4. Proses Absensi
        // Ganti Auth::id() dengan angka statis (misal 1) JIKA Anda belum mengaktifkan sistem Login
        $userId = Auth::id() ?? 1; // Fallback ke user 1 jika belum login untuk testing

        $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
                             ->where('user_id', $userId)
                             ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status'  => 'warning',
                'message' => 'Anda sudah melakukan absensi untuk kegiatan: ' . $kegiatan->nama_kegiatan
            ], 200);
        }

        Absensi::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id'     => $userId,
            'waktu_hadir' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil absen untuk kegiatan: ' . $kegiatan->nama_kegiatan
        ], 200);
    }

    public function apiTotalHadir(Request $request)
    {
        // Mengambil semua ID yang ada di halaman untuk dihitung sekaligus
        $ids = $request->ids; 
        
        $counts = Absensi::select('kegiatan_id', \DB::raw('count(*) as total'))
                    ->whereIn('kegiatan_id', $ids)
                    ->groupBy('kegiatan_id')
                    ->get()
                    ->pluck('total', 'kegiatan_id');

        return response()->json($counts);
    }

    // public function getTotalHadir($id)
    // {
    //     $total = Absensi::where('kegiatan_id', $id)->count();

    //     return response()->json([
    //         'total' => $total
    //     ]);
    // }

    // public function getTotalHadir($id)
    // {
    //     // Ambil data absensi terbaru beserta user-nya
    //     $absensi = \App\Models\Absensi::with('user')
    //                 ->where('kegiatan_id', $id)
    //                 ->latest()
    //                 ->get();

    //     return response()->json([
    //         'total' => $absensi->count(),
    //         'names' => $absensi->map(function($item) {
    //             return [
    //                 'name' => $item->user->name, // Mengambil nama dari relasi user
    //                 'waktu' => \Carbon\Carbon::parse($item->waktu_hadir)->format('H:i:s') // Format jam:menit:detik
    //             ];
    //         })
    //     ]);
    // }

    public function getTotalHadir($id)
    {
        // Ambil data absensi terbaru beserta user-nya
        $absensi = \App\Models\Absensi::with('user')
                    ->where('kegiatan_id', $id)
                    ->latest()
                    ->get();

        return response()->json([
            'total' => $absensi->count(),
            'data'  => $absensi->map(function($item) {
                return [
                    'name'  => $item->user->name,
                    // Pastikan format waktu sesuai keinginan Bapak
                    'waktu' => \Carbon\Carbon::parse($item->waktu_hadir)->format('H:i:s')
                ];
            })
        ]);
    }
}