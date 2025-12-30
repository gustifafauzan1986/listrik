<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Jobs\SendWhatsappJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache; // PENTING: Tambahkan ini untuk fitur Cache

class WhatsappWebhookController extends Controller
// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Validasi input dasar
//         $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//         $messageRaw = trim($request->input('message'));
//         $sessionId = $request->input('session_id');

//         if (!$senderNumber || !$messageRaw) {
//             return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//         }

//         // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//         $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
        
//         // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//         // Sesuaikan logika ini dengan format penyimpanan di DB Anda
//         $phoneLocal = $phone;
//         if (substr($phone, 0, 2) == '62') {
//             $phoneLocal = '0' . substr($phone, 2);
//         }

//         // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//         $student = Student::where('phone', $phoneLocal)
//                     ->orWhere('parent_phone', $phoneLocal)
//                     ->orWhere('parent_phone_2', $phoneLocal)
//                     ->first();

//         if (!$student) {
//             // Log nomor tak dikenal untuk analisis
//             Log::info("WA Ignored: Nomor $phone tidak terdaftar.");
//             return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//         }

//         // --- LOGIKA CHATBOT ---
//         $message = strtoupper($messageRaw);

//         try {
//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
            
//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
            
//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan "Ketik MENU untuk bantuan"
//                 // $this->sendText($phone, "Maaf perintah tidak dikenali. Ketik *MENU* untuk melihat pilihan.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Exception $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json(['status' => 'error'], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => "Halo, Bapak/Ibu dari *{$student->name}*.\nSilakan pilih menu informasi di bawah ini:",
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         Http::post($this->nodeServerUrl, $payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         // $absen = Attendance::where('student_id', $student->id)->whereDate('created_at', now())->first();
//         // $status = $absen ? $absen->status : 'Belum Absen';
        
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         Http::post($this->nodeServerUrl, [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
            
//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
            
//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
            
//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());
            
//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => "Halo, Bapak/Ibu dari *{$student->name}*.\nSilakan pilih menu informasi di bawah ini:",
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             $response = Http::post($this->nodeServerUrl, $payload);
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan."); 
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
//             // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             if (Cache::has($cacheKey)) {
//                 // Log untuk debug bahwa duplikat dicegah
//                 Log::info("WA Ignored: Duplicate message detected from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }

//             // Kunci pesan ini selama 5 detik
//             Cache::put($cacheKey, true, 5); 


//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
            
//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
            
//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
            
//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());
            
//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => "Halo, Bapak/Ibu dari *{$student->name}*.\nSilakan pilih menu informasi di bawah ini:",
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             // Tambahkan timeout agar controller tidak hang jika node.js lambat
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
            
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan."); 
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
//             // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             // FIX: Menggunakan Fully Qualified Class Name (\Illuminate\...) 
//             // untuk mencegah error "Class Cache not found" jika namespace berbeda
//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 // Log untuk debug bahwa duplikat dicegah
//                 Log::info("WA Ignored: Duplicate message detected from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }

//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5); 


//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
            
//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
            
//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
            
//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());
            
//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         // CATATAN PENTING:
//         // List Message (Menu Tombol) TIDAK MUNCUL di WhatsApp Web/Desktop.
//         // Menu hanya muncul di Aplikasi HP (Android/iOS).
//         // Oleh karena itu, kita tambahkan teks instruksi di 'message' sebagai fallback.

//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan pilih menu informasi dengan menekan tombol di bawah, " .
//                      "atau ketik perintah berikut jika tombol tidak muncul:\n\n" .
//                      "👉 Ketik *CEK_ABSENSI_HARI_INI*\n" .
//                      "👉 Ketik *CEK_JADWAL*\n" .
//                      "👉 Ketik *CEK_SPP*";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu Pilihan",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             // Tambahkan timeout agar controller tidak hang jika node.js lambat
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
            
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan."); 
//         }
//     }
// }

{
    // URL Node.js Server (Sesuaikan port dengan index.js)
    protected $nodeServerUrl = 'http://localhost:3000/send-message';

    public function handle(Request $request)
    {
        // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
        try {
            // Validasi input dasar
            $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
            $messageRaw = trim($request->input('message'));
            $sessionId = $request->input('session_id');

            if (!$senderNumber || !$messageRaw) {
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
            // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
            // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
            $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

            // FIX: Menggunakan Fully Qualified Class Name (\Illuminate\...) 
            // untuk mencegah error "Class Cache not found" jika namespace berbeda
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                // Log untuk debug bahwa duplikat dicegah
                Log::info("WA Ignored: Duplicate message detected from $senderNumber");
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
            }

            // Kunci pesan ini selama 5 detik
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5); 


            // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
            $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
            
            // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
            $phoneLocal = $phone;
            if (substr($phone, 0, 2) == '62') {
                $phoneLocal = '0' . substr($phone, 2);
            }

            // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
            // Pastikan model Student ada. Jika error di sini, cek database Anda.
            $student = Student::where('phone', $phoneLocal)
                        // ->orWhere('parent_phone', $phoneLocal)
                        // ->orWhere('parent_phone_2', $phoneLocal)
                        ->first();

            if (!$student) {
                // Log nomor tak dikenal untuk analisis
                Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
                return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
            }

            // --- LOGIKA CHATBOT ---
            $message = strtoupper($messageRaw);

            // A. Trigger Menu Utama
            if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
                $this->sendMenu($phone, $student, $sessionId);
            }
            
            // B. Cek Absensi (Triggered by List Button ID)
            elseif ($message === 'CEK_ABSENSI_HARI_INI') {
                $this->cekAbsensiHarian($phone, $student, $sessionId);
            }

            // C. Cek Jadwal (Triggered by List Button ID)
            elseif ($message === 'CEK_JADWAL') {
                $this->cekJadwal($phone, $student, $sessionId);
            }
            
            // D. Fallback jika perintah tidak dikenali
            else {
                // Opsional: Kirim pesan balasan default
                // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
            }

            return response()->json(['status' => 'processed']);

        } catch (\Throwable $e) {
            // Tangkap Error System (Database, Model not found, dll)
            Log::error("WA Error: " . $e->getMessage());
            
            // Kembalikan pesan error asli untuk debugging di Postman
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Mengirim Menu Pilihan (List Message)
     */
    private function sendMenu($phone, $student, $sessionId)
    {
        // CATATAN PENTING:
        // List Message (Menu Tombol) TIDAK MUNCUL di WhatsApp Web/Desktop.
        // Menu hanya muncul di Aplikasi HP (Android/iOS).
        // Oleh karena itu, kita tambahkan teks instruksi di 'message' sebagai fallback.

        $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
                     "Silakan klik tombol *MENU PILIHAN* di bawah. \n\n" .
                     "⚠️ _Jika tombol tidak muncul (WA Web/Laptop), ketik perintah:_ \n" .
                     "👉 *CEK_ABSENSI_HARI_INI*\n" .
                     "👉 *CEK_JADWAL*\n" .
                     "👉 *CEK_SPP*";

        $payload = [
            'session_id' => $sessionId,
            'number' => $phone,
            'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
            'message' => $pesanTeks,
            'title' => "Sistem Informasi Akademik",
            'footer' => "SMK Teladan - Bot Otomatis",
            'buttonText' => "MENU PILIHAN",
            'sections' => [
                [
                    'title' => 'Informasi Siswa',
                    'rows' => [
                        [
                            'title' => 'Cek Absensi Hari Ini',
                            'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
                            'description' => 'Status kehadiran siswa hari ini'
                        ],
                        [
                            'title' => 'Jadwal Pelajaran',
                            'rowId' => 'CEK_JADWAL',
                            'description' => 'Mata pelajaran hari ini'
                        ]
                    ]
                ],
                [
                    'title' => 'Administrasi',
                    'rows' => [
                         [
                            'title' => 'Info SPP',
                            'rowId' => 'CEK_SPP',
                            'description' => 'Cek tagihan SPP bulan berjalan'
                        ]
                    ]
                ]
            ]
        ];

        // Kirim ke Node.js
        $this->postToNode($payload);
    }

    /**
     * Contoh Logika Cek Absensi
     */
    private function cekAbsensiHarian($phone, $student, $sessionId)
    {
        // Logika query absensi di sini (contoh dummy)
        $status = "HADIR (07:05 WIB)"; // Dummy data

        $message = "Laporan Absensi Hari Ini:\n\n" .
                   "Nama: *{$student->name}*\n" .
                   "Tanggal: " . date('d-m-Y') . "\n" .
                   "Status: *{$status}*";

        $this->sendText($phone, $message, $sessionId);
    }

    /**
     * Contoh Logika Cek Jadwal
     */
    private function cekJadwal($phone, $student, $sessionId)
    {
        $message = "Jadwal Pelajaran Hari Ini:\n\n" .
                   "1. Matematika\n" .
                   "2. Bahasa Indonesia\n" .
                   "3. Pemrograman Web";

        $this->sendText($phone, $message, $sessionId);
    }

    /**
     * Helper untuk kirim pesan teks biasa
     */
    private function sendText($phone, $message, $sessionId)
    {
        $this->postToNode([
            'session_id' => $sessionId,
            'number' => $phone,
            'type' => 'text',
            'message' => $message
        ]);
    }

    /**
     * Wrapper Http Request ke Node.js
     */
    private function postToNode($payload)
    {
        try {
            // Tambahkan timeout agar controller tidak hang jika node.js lambat
            $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
            
            if ($response->failed()) {
                Log::error("Gagal kirim ke Node.js: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Koneksi Node.js Refused: " . $e->getMessage());
            // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
            throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan."); 
        }
    }
}