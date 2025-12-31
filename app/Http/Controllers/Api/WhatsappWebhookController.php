<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Attendance;
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
//                      "Silakan klik tombol *MENU PILIHAN* di bawah. \n\n" .
//                      "⚠️ _Jika tombol tidak muncul (WA Web/Laptop), ketik perintah:_ \n" .
//                      "👉 *CEK_ABSENSI_HARI_INI*\n" .
//                      "👉 *CEK_JADWAL*\n" .
//                      "👉 *CEK_SPP*";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
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

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
//             // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }
//             // C. Cek Jadwal (Via Tombol atau Angka '2')
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
//             // D. Cek SPP (Via Tombol atau Angka '3')
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phone, $student, $sessionId);
//             }
//             // E. Rekap Mingguan (Angka 4)
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'weekly');
//             }
//             // F. Rekap Bulanan (Angka 5)
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'monthly');
//             }
//             // G. Rekap Semester (Angka 6)
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'semester');
//             }

//             // H. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default jika user mengetik sembarangan
//                 $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik *MENU* untuk melihat pilihan.", $sessionId);
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

//     // {
//     //     try {
//     //         // Validasi input
//     //         $senderNumber = $request->input('from'); 
//     //         $messageRaw = trim($request->input('message'));
//     //         $sessionId = $request->input('session_id');

//     //         if (!$senderNumber || !$messageRaw) {
//     //             return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//     //         }

//     //         // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//     //         $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//     //         if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//     //             Log::info("WA Ignored: Duplicate message from $senderNumber");
//     //             return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//     //         }
//     //         // Kunci pesan ini selama 5 detik
//     //         \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5); 

//     //         // 1. Bersihkan nomor
//     //         $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
//     //         $phoneLocal = $phone;
//     //         if (substr($phone, 0, 2) == '62') {
//     //             $phoneLocal = '0' . substr($phone, 2);
//     //         }

//     //         // 2. Cari Siswa
//     //         $student = Student::where('phone', $phoneLocal)
//     //                     ->orWhere('parent_phone', $phoneLocal)
//     //                     ->orWhere('parent_phone_2', $phoneLocal)
//     //                     ->first();

//     //         if (!$student) {
//     //             Log::info("WA Ignored: Nomor $phone tidak terdaftar.");
//     //             return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//     //         }

//     //         // --- LOGIKA CHATBOT ---
//     //         $message = strtoupper($messageRaw);

//     //         // A. Menu Utama
//     //         if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//     //             $this->sendMenu($phone, $student, $sessionId);
//     //         }
//     //         // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//     //         elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//     //             $this->cekAbsensiHarian($phone, $student, $sessionId);
//     //         }
//     //         // C. Cek Jadwal (Via Tombol atau Angka '2')
//     //         elseif ($message === 'CEK_JADWAL' || $message === '2') {
//     //             $this->cekJadwal($phone, $student, $sessionId);
//     //         }
//     //         // D. Cek SPP (Via Tombol atau Angka '3')
//     //         elseif ($message === 'CEK_SPP' || $message === '3') {
//     //             $this->cekSPP($phone, $student, $sessionId);
//     //         }
//     //         // E. Rekap Mingguan (Angka 4)
//     //         elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//     //             $this->cekRekapAbsensi($phone, $student, $sessionId, 'weekly');
//     //         }
//     //         // F. Rekap Bulanan (Angka 5)
//     //         elseif ($message === 'REKAP_BULAN' || $message === '5') {
//     //             $this->cekRekapAbsensi($phone, $student, $sessionId, 'monthly');
//     //         }
//     //         // G. Rekap Semester (Angka 6)
//     //         elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//     //             $this->cekRekapAbsensi($phone, $student, $sessionId, 'semester');
//     //         }

//     //         return response()->json(['status' => 'processed']);

//     //     } catch (\Throwable $e) {
//     //         Log::error("WA Error: " . $e->getMessage());
//     //         return response()->json([
//     //             'status' => 'error', 
//     //             'message' => $e->getMessage(),
//     //             'file' => basename($e->getFile())
//     //         ], 500);
//     //     }
//     // }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     // {
//     //     // CATATAN PENTING:
//     //     // List Message (Menu Tombol) TIDAK MUNCUL di WhatsApp Web/Desktop.
//     //     // Menu hanya muncul di Aplikasi HP (Android/iOS).
//     //     // Oleh karena itu, kita tambahkan teks instruksi angka di 'message' sebagai fallback.

//     //     $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//     //                  "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//     //                  "1️⃣ Ketik *1* : Laporan Harian Siswa\n" .
//     //                  "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//     //                  "3️⃣ Ketik *3* : Info Tagihan SPP\n\n" .
//     //                  "Atau tekan tombol di bawah jika menggunakan HP:";

//     //     $payload = [
//     //         'session_id' => $sessionId,
//     //         'number' => $phone,
//     //         'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//     //         'message' => $pesanTeks,
//     //         'title' => "Sistem Informasi Sekolah",
//     //         'footer' => "SMK GATECH - Bot Otomatis",
//     //         'buttonText' => "MENU PILIHAN",
//     //         'sections' => [
//     //             [
//     //                 'title' => 'Informasi Siswa',
//     //                 'rows' => [
//     //                     [
//     //                         'title' => 'Laporan Harian Siswa',
//     //                         'rowId' => 'LAPORAN_HARIAN', // ID ini akan dikirim balik saat diklik
//     //                         'description' => 'Balas 1'
//     //                     ],
//     //                     [
//     //                         'title' => 'Jadwal Pelajaran',
//     //                         'rowId' => 'CEK_JADWAL',
//     //                         'description' => 'Balas 2'
//     //                     ]
//     //                 ]
//     //             ],
//     //             [
//     //                 'title' => 'Administrasi',
//     //                 'rows' => [
//     //                      [
//     //                         'title' => 'Info SPP',
//     //                         'rowId' => 'CEK_SPP',
//     //                         'description' => 'Balas 3'
//     //                     ]
//     //                 ]
//     //             ]
//     //         ]
//     //     ];

//     //     // Kirim ke Node.js
//     //     $this->postToNode($payload);
//     // }

//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//                      "3️⃣ Ketik *3* : Info Tagihan SPP\n" .
//                      "----------------------------------\n" .
//                      "📊 *MENU REKAPITULASI:*\n" .
//                      "4️⃣ Ketik *4* : Rekap Absensi Minggu Ini\n" .
//                      "5️⃣ Ketik *5* : Rekap Absensi Bulan Ini\n" .
//                      "6️⃣ Ketik *6* : Rekap Absensi Semester Ini\n\n" .
//                      "Atau tekan tombol di bawah jika menggunakan HP:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', 
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => 'CEK_ABSENSI_HARI_INI', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => 'CEK_JADWAL', 'description' => 'Balas 2'],
//                         ['title' => 'Info SPP', 'rowId' => 'CEK_SPP', 'description' => 'Balas 3']
//                     ]
//                 ],
//                 [
//                     'title' => 'Laporan Rekapitulasi',
//                     'rows' => [
//                         ['title' => 'Rekap Mingguan', 'rowId' => 'REKAP_MINGGU', 'description' => 'Balas 4'],
//                         ['title' => 'Rekap Bulanan', 'rowId' => 'REKAP_BULAN', 'description' => 'Balas 5'],
//                         ['title' => 'Rekap Semester', 'rowId' => 'REKAP_SEMESTER', 'description' => 'Balas 6']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiGerbang($phone, $student, $sessionId)
//     // {
//     //     $today = Carbon::today();
        
//     //     // Cari data absensi siswa ini untuk tanggal hari ini
//     //     // Asumsi struktur tabel: student_id, status (hadir/sakit/izin), created_at
//     //     $attendance = DailyAttendance::where('student_id', $student->id)
//     //                                  ->whereDate('created_at', $today)
//     //                                  ->first();

//     //     if ($attendance) {
//     //         $jamMasuk = Carbon::parse($attendance->arrival_time)->format('H:i');
//     //         $statusRaw = strtoupper($attendance->status); // HADIR, TELAT, DLL
//     //         $statusDisplay = "{$statusRaw} (Pukul {$jamMasuk})";
//     //     } else {
//     //         $statusDisplay = "BELUM ABSEN / ALPA";
//     //     }

//     //     $message = "Laporan Absensi Gerbang Hari Ini:\n\n" .
//     //                "Nama: *{$student->name}*\n" .
//     //                "Tanggal: " . $today->format('d-m-Y') . "\n" .
//     //                "Status: *{$statusDisplay}*";

//     //     $this->sendText($phone, $message, $sessionId);

//     // }

//     // {
//     //     $today = Carbon::today();

//     //     // --- 1. KEHADIRAN GERBANG ---
//     //     $gate = DailyAttendance::where('student_id', $student->id)
//     //                             ->whereDate('created_at', $today)
//     //                             ->first();

//     //     if ($gate) {
//     //         // Gunakan arrival_time jika ada, fallback ke created_at
//     //         $waktu = $gate->arrival_time ?? $gate->created_at;
//     //         $jamMasuk = Carbon::parse($waktu)->format('H:i');
//     //         $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
//     //     } else {
//     //         $statusGerbang = "BELUM ABSEN / ALPA";
//     //     }

//     //     // --- 2. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//     //     // Asumsi: Anda memiliki model 'SubjectAttendance' untuk absensi per mapel
//     //     // Pastikan model SubjectAttendance di-import atau gunakan path lengkap
//     //     // Jika tabel belum ada, bagian ini akan return list kosong atau error (bisa dikomentari jika belum siap)
        
//     //     $listPembelajaran = "";
        
//     //     try {
//     //         // Mencari data absensi mapel siswa hari ini
//     //         // Relasi 'subject' diasumsikan ada di model SubjectAttendance
//     //         $lessons = \App\Models\Attendance::with('subject') 
//     //                                 ->where('student_id', $student->id)
//     //                                 ->whereDate('created_at', $today)
//     //                                 ->orderBy('created_at', 'asc')
//     //                                 ->get();

//     //         if ($lessons->count() > 0) {
//     //             foreach ($lessons as $lesson) {
//     //                 // Ambil nama mapel. Jika relasi null, cek kolom subject_name (tergantung DB Anda)
//     //                 $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//     //                 $statusLesson = strtoupper($lesson->status); // HADIR, IZIN, SAKIT
//     //                 $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
                    
//     //                 $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//     //             }
//     //         } else {
//     //             $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//     //         }
//     //     } catch (\Throwable $e) {
//     //         // Fallback jika tabel/model SubjectAttendance belum ada agar bot tidak crash
//     //         $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//     //         Log::error("SubjectAttendance Error: " . $e->getMessage());
//     //     }

//     //     // --- SUSUN PESAN FINAL ---
//     //     $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//     //                "Nama: *{$student->name}*\n" .
//     //                "Tanggal: " . $today->format('d-m-Y') . "\n\n" .
//     //                "🏫 *ABSENSI GERBANG*\n" .
//     //                "Status: *{$statusGerbang}*\n\n" .
//     //                "📚 *ABSENSI PEMBELAJARAN*\n" .
//     //                $listPembelajaran;

//     //     $this->sendText($phone, $message, $sessionId);
//     // }

//     {
//         $today = Carbon::today();
//         $hariIndo = [
//             'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
//             'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
//         ];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // --- 1. KEHADIRAN GERBANG ---
//         $gate = DailyAttendance::where('student_id', $student->id)
//                                 ->whereDate('created_at', $today)
//                                 ->first();

//         if ($gate) {
//             $waktuDatang = $gate->arrival_time ?? $gate->created_at;
//             $waktuPulang = $gate->departure_time;
//             if($waktuPulang !== null){
//                 $jamPulang = Carbon::parse($waktuPulang)->format('H:i');
//             }else{
//                 $jamPulang = 'Belum Pulang';
//             }
//             $jamMasuk = Carbon::parse($waktuDatang)->format('H:i');
            
//             $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk}) - (Pulang: {$jamPulang})";
//         } else {
//             $statusGerbang = "BELUM ABSEN / ALPA";
//         }

//         // --- 2. JADWAL PELAJARAN (Hari Ini) ---
//         $listJadwal = "";
//         try {
//             // Asumsi: Model Schedule memiliki relasi 'subject' dan 'teacher'
//             // Sesuaikan namespace model dan nama relasi dengan database Anda
//             $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
//                                     ->where('classroom_id', $student->classroom_id)
//                                     ->where('day', $namaHari)
//                                     ->orderBy('start_time', 'asc')
//                                     ->get();

//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $mapel = $sched->subject->name ?? 'Mapel';
//                     $guru = $sched->teacher->name ?? '-'; // Menambahkan Nama Guru
//                     $jamMulai = substr($sched->start_time, 0, 5);
//                     $jamSelesai = substr($sched->end_time, 0, 5);
                    
//                     // Format: Mapel (07:00-08:00) - Guru
//                     $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
//                 }
//             } else {
//                 // Jika tidak ada data di DB, tampilkan pesan default
//                 $listJadwal = "_Tidak ada jadwal pelajaran di database_";
//             }
//         } catch (\Throwable $e) {
//             // Fallback jika tabel/model Schedule belum ada atau relasi guru tidak ada
//             $listJadwal = "_Data jadwal belum tersedia / Error: " . $e->getMessage() . "_";
//         }

//         // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//         $listPembelajaran = "";
//         try {
//             $lessons = \App\Models\Attendance::with('subject') 
//                                     ->where('student_id', $student->id)
//                                     ->whereDate('created_at', $today)
//                                     ->orderBy('created_at', 'asc')
//                                     ->get();

//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $lesson) {
//                     $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//                     $statusLesson = strtoupper($lesson->status); // HADIR, IZIN, SAKIT
//                     $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
                    
//                     $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//                 }
//             } else {
//                 $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//             }
//         } catch (\Throwable $e) {
//             $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//         }

//         // --- SUSUN PESAN FINAL ---
//         $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Hari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n" .
//                    "🏫 *KEHADIRAN GERBANG*\n" .
//                    "Status: *{$statusGerbang}*\n\n" .
//                    "📅 *JADWAL HARI INI*\n" .
//                    $listJadwal . "\n" .
//                    "📚 *KEHADIRAN PEMBELAJARAN*\n" .
//                    $listPembelajaran;

//         $this->sendText($phone, $message, $sessionId);
//     }


//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         $endDate = Carbon::now();
//         $label = "";

//         // Tentukan Rentang Waktu
//         if ($period == 'weekly') {
//             $startDate = Carbon::now()->subDays(7);
//             $label = "MINGGU INI (7 Hari Terakhir)";
//         } elseif ($period == 'monthly') {
//             $startDate = Carbon::now()->startOfMonth();
//             $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
//         } elseif ($period == 'semester') {
//             // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
//             $month = Carbon::now()->month;
//             if ($month >= 7) {
//                 $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
//                 $label = "SEMESTER GANJIL";
//             } else {
//                 $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
//                 $label = "SEMESTER GENAP";
//             }
//         }

//         // 1. REKAP GERBANG (Hitung jumlah Status)
//         // Output: [HADIR => 5, SAKIT => 1, ...]
//         $rekapGerbang = DailyAttendance::where('student_id', $student->id)
//                         ->whereBetween('created_at', [$startDate, $endDate])
//                         ->selectRaw('status, count(*) as total')
//                         ->groupBy('status')
//                         ->pluck('total', 'status')
//                         ->toArray();

//         // Format Text Gerbang
//         $txtGerbang = "";
//         $totalKehadiran = 0;
//         if (empty($rekapGerbang)) {
//             $txtGerbang = "_Belum ada data absensi_";
//         } else {
//             foreach ($rekapGerbang as $status => $total) {
//                 $statusUpper = strtoupper($status);
//                 $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
//                 $totalKehadiran += $total;
//             }
//             $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
//         }

//         // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
//         try {
//             $totalMapel = Attendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, $endDate])
//                             ->where('status', 'HADIR') // Asumsi hanya menghitung kehadiran
//                             ->count();
            
//             $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
//         } catch (\Throwable $e) {
//             $txtMapel = "-";
//         }

//         // SUSUN PESAN
//         $message = "📈 *LAPORAN REKAPITULASI*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Periode: {$label}\n\n" .
//                    "🏫 *ABSENSI GERBANG*\n" .
//                    $txtGerbang . "\n\n" .
//                    "📚 *ABSENSI PEMBELAJARAN*\n" .
//                    $txtMapel . "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

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
//      * Contoh Logika Cek SPP (Baru Ditambahkan)
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         // Logika query SPP di sini (contoh dummy)
//         $message = "Info Keuangan Siswa:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Bulan: " . date('F Y') . "\n" .
//                    "Status SPP: *LUNAS*\n" .
//                    "Tunggakan Lain: Rp 0,-";

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
//     // URL Node.js Server
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         try {
//             // Validasi input
//             $senderNumber = $request->input('from'); 
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 Log::info("WA Ignored: Duplicate message from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }
//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5); 

//             // 1. Bersihkan nomor
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
//             // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }
//             // C. Cek Jadwal (Via Tombol atau Angka '2')
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
//             // D. Cek SPP (Via Tombol atau Angka '3')
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phone, $student, $sessionId);
//             }
//             // E. Rekap Mingguan (Angka 4)
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'weekly');
//             }
//             // F. Rekap Bulanan (Angka 5)
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'monthly');
//             }
//             // G. Rekap Semester (Angka 6)
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'semester');
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => $e->getMessage(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//                      "3️⃣ Ketik *3* : Info Tagihan SPP\n" .
//                      "----------------------------------\n" .
//                      "📊 *MENU REKAPITULASI:*\n" .
//                      "4️⃣ Ketik *4* : Rekap Absensi Minggu Ini\n" .
//                      "5️⃣ Ketik *5* : Rekap Absensi Bulan Ini\n" .
//                      "6️⃣ Ketik *6* : Rekap Absensi Semester Ini\n\n" .
//                      "Atau tekan tombol di bawah jika menggunakan HP:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', 
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => 'CEK_ABSENSI_HARI_INI', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => 'CEK_JADWAL', 'description' => 'Balas 2'],
//                         ['title' => 'Info SPP', 'rowId' => 'CEK_SPP', 'description' => 'Balas 3']
//                     ]
//                 ],
//                 [
//                     'title' => 'Laporan Rekapitulasi',
//                     'rows' => [
//                         ['title' => 'Rekap Mingguan', 'rowId' => 'REKAP_MINGGU', 'description' => 'Balas 4'],
//                         ['title' => 'Rekap Bulanan', 'rowId' => 'REKAP_BULAN', 'description' => 'Balas 5'],
//                         ['title' => 'Rekap Semester', 'rowId' => 'REKAP_SEMESTER', 'description' => 'Balas 6']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * Cek Absensi Lengkap (Gerbang, Jadwal & Pembelajaran) - Harian
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         $today = Carbon::today();
//         $hariIndo = [
//             'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
//             'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
//         ];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // --- 1. KEHADIRAN GERBANG ---
//         $gate = DailyAttendance::where('student_id', $student->id)
//                                 ->whereDate('created_at', $today)
//                                 ->first();

//         if ($gate) {
//             $waktu = $gate->arrival_time ?? $gate->created_at;
//             $jamMasuk = Carbon::parse($waktu)->format('H:i');
//             $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
//         } else {
//             $statusGerbang = "BELUM ABSEN / ALPA";
//         }

//         // --- 2. JADWAL PELAJARAN (Hari Ini) ---
//         $listJadwal = "";
//         try {
//             $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
//                                     ->where('classroom_id', $student->classroom_id)
//                                     ->where('day', $namaHari)
//                                     ->orderBy('start_time', 'asc')
//                                     ->get();

//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $mapel = $sched->subject->name ?? 'Mapel';
//                     $guru = $sched->teacher->name ?? '-';
//                     $jamMulai = substr($sched->start_time, 0, 5);
//                     $jamSelesai = substr($sched->end_time, 0, 5);
//                     $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
//                 }
//             } else {
//                 $listJadwal = "_Tidak ada jadwal pelajaran_";
//             }
//         } catch (\Throwable $e) {
//             $listJadwal = "_Data jadwal belum tersedia_";
//         }

//         // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//         $listPembelajaran = "";
//         try {
//             $lessons = \App\Models\Attendance::with('subject') 
//                                     ->where('student_id', $student->id)
//                                     ->whereDate('created_at', $today)
//                                     ->orderBy('created_at', 'asc')
//                                     ->get();

//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $lesson) {
//                     $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//                     $statusLesson = strtoupper($lesson->status);
//                     $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
//                     $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//                 }
//             } else {
//                 $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//             }
//         } catch (\Throwable $e) {
//             $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//         }

//         // --- SUSUN PESAN FINAL ---
//         $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Hari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n" .
//                    "🏫 *KEHADIRAN GERBANG*\n" .
//                    "Status: *{$statusGerbang}*\n\n" .
//                    "📅 *JADWAL HARI INI*\n" .
//                    $listJadwal . "\n" .
//                    "📚 *KEHADIRAN PEMBELAJARAN*\n" .
//                    $listPembelajaran. "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Hitung Rekapitulasi Absensi (Mingguan/Bulanan/Semester)
//      */
//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         $endDate = Carbon::now();
//         $label = "";

//         // Tentukan Rentang Waktu
//         if ($period == 'weekly') {
//             $startDate = Carbon::now()->subDays(7);
//             $label = "MINGGU INI (7 Hari Terakhir)";
//         } elseif ($period == 'monthly') {
//             $startDate = Carbon::now()->startOfMonth();
//             $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
//         } elseif ($period == 'semester') {
//             // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
//             $month = Carbon::now()->month;
//             if ($month >= 7) {
//                 $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
//                 $label = "SEMESTER GANJIL";
//             } else {
//                 $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
//                 $label = "SEMESTER GENAP";
//             }
//         }

//         // 1. REKAP GERBANG (Hitung jumlah Status)
//         // Output: [HADIR => 5, SAKIT => 1, ...]
//         $rekapGerbang = DailyAttendance::where('student_id', $student->id)
//                         ->whereBetween('created_at', [$startDate, $endDate])
//                         ->selectRaw('status, count(*) as total')
//                         ->groupBy('status')
//                         ->pluck('total', 'status')
//                         ->toArray();

//         // Format Text Gerbang
//         $txtGerbang = "";
//         $totalKehadiran = 0;
//         if (empty($rekapGerbang)) {
//             $txtGerbang = "_Belum ada data absensi_";
//         } else {
//             foreach ($rekapGerbang as $status => $total) {
//                 $statusUpper = strtoupper($status);
//                 $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
//                 $totalKehadiran += $total;
//             }
//             $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
//         }

//         // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
//         try {
//             $totalMapel = Attendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, $endDate])
//                             ->where('status', 'hadir') // Asumsi hanya menghitung kehadiran
//                             ->count();
            
//             $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
//         } catch (\Throwable $e) {
//             $txtMapel = "-";
//         }

//         // SUSUN PESAN
//         $message = "📈 *LAPORAN REKAPITULASI*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Periode: {$label}\n\n" .
//                    "🏫 *ABSENSI GERBANG*\n" .
//                    $txtGerbang . "\n\n" .
//                    "📚 *ABSENSI PEMBELAJARAN*\n" .
//                    $txtMapel . "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Logika Cek Jadwal
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
//      * Logika Cek SPP
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         $message = "Info Keuangan Siswa:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Bulan: " . date('F Y') . "\n" .
//                    "Status SPP: *LUNAS*\n" .
//                    "Tunggakan Lain: Rp 0,-";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper kirim pesan teks
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
//      * Wrapper Http Request
//      */
//     private function postToNode($payload)
//     {
//         try {
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan."); 
//         }
//     }
// }

{
    // URL Node.js Server
    protected $nodeServerUrl = 'http://localhost:3000/send-message';

    public function handle(Request $request)
    {
        try {
            // Validasi input
            $senderNumber = $request->input('from'); 
            $messageRaw = trim($request->input('message'));
            $sessionId = $request->input('session_id');

            if (!$senderNumber || !$messageRaw) {
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
            $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                Log::info("WA Ignored: Duplicate message from $senderNumber");
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
            }
            // Kunci pesan ini selama 5 detik
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5); 

            // 1. Normalisasi Nomor Telepon (Buat 2 Versi: 08xx dan 628xx)
            // Bersihkan suffix @s.whatsapp.net
            $phonePure = str_replace('@s.whatsapp.net', '', $senderNumber); // Contoh: 62812345678
            
            $phone62 = $phonePure; // Versi 62 (default dari WA)
            $phone0 = $phonePure;  // Versi 0 (format lokal)

            // Jika diawali 62, buat versi 0-nya
            if (substr($phonePure, 0, 2) == '62') {
                $phone0 = '0' . substr($phonePure, 2);
            }
            // Jika diawali 0, buat versi 62-nya (jaga-jaga input aneh)
            elseif (substr($phonePure, 0, 1) == '0') {
                $phone62 = '62' . substr($phonePure, 1);
            }

            // 2. Cari Siswa (Cari KEDUA format di SEMUA kolom telepon)
            // Ini memastikan nomor ketemu tidak peduli admin simpan pakai 08 atau 62
            $student = Student::where(function($query) use ($phone0, $phone62) {
                            $query->where('phone', $phone0)
                                  ->orWhere('phone', $phone62);
                        })
                        // ->orWhere(function($query) use ($phone0, $phone62) {
                        //     $query->where('parent_phone', $phone0)
                        //           ->orWhere('parent_phone', $phone62);
                        // })
                        // ->orWhere(function($query) use ($phone0, $phone62) {
                        //     $query->where('parent_phone_2', $phone0)
                        //           ->orWhere('parent_phone_2', $phone62);
                        // })
                        ->first();

            if (!$student) {
                
                // --- FIX: Tetap balas pesan meskipun nomor tidak dikenal ---
                // Ini penting agar Anda tahu botnya HIDUP, hanya datanya yang tidak ketemu.
                $this->sendText($phonePure, "Maaf, nomor Anda ($phone0) belum terdaftar di sistem sekolah. Silakan hubungi Tata Usaha untuk update data.", $sessionId);
                // Log detail agar Anda tahu nomor mana yang gagal dikenali
                Log::info("WA Ignored: Nomor $phonePure (Cek: $phone0 / $phone62) tidak ditemukan di database.");
                return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
            }

            // --- LOGIKA CHATBOT ---
            $message = strtoupper($messageRaw);

            // A. Menu Utama
            if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
                $this->sendMenu($phonePure, $student, $sessionId);
            }
            // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
            elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
                $this->cekAbsensiHarian($phonePure, $student, $sessionId);
            }
            // C. Cek Jadwal (Via Tombol atau Angka '2')
            elseif ($message === 'CEK_JADWAL' || $message === '2') {
                $this->cekJadwal($phonePure, $student, $sessionId);
            }
            // D. Cek SPP (Via Tombol atau Angka '3')
            elseif ($message === 'CEK_SPP' || $message === '3') {
                $this->cekSPP($phonePure, $student, $sessionId);
            }
            // E. Rekap Mingguan (Angka 4)
            elseif ($message === 'REKAP_MINGGU' || $message === '4') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'weekly');
            }
            // F. Rekap Bulanan (Angka 5)
            elseif ($message === 'REKAP_BULAN' || $message === '5') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'monthly');
            }
            // G. Rekap Semester (Angka 6)
            elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'semester');
            }
            else {
                // Opsional: Kirim pesan balasan default
                $this->sendText($phonePure, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
            }

            return response()->json(['status' => 'processed']);

        } catch (\Throwable $e) {
            Log::error("WA Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Mengirim Menu Pilihan
     */
    private function sendMenu($phone, $student, $sessionId)
    {
        $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
                     "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
                     "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
                     "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
                     "3️⃣ Ketik *3* : Info Tagihan SPP\n" .
                     "----------------------------------\n" .
                     "📊 *MENU REKAPITULASI:*\n" .
                     "4️⃣ Ketik *4* : Rekap Absensi Minggu Ini\n" .
                     "5️⃣ Ketik *5* : Rekap Absensi Bulan Ini\n" .
                     "6️⃣ Ketik *6* : Rekap Absensi Semester Ini\n\n" .
                     "Atau tekan tombol di bawah jika menggunakan HP:";

        $payload = [
            'session_id' => $sessionId,
            'number' => $phone,
            'type' => 'list', 
            'message' => $pesanTeks,
            'title' => "Sistem Informasi Akademik",
            'footer' => "SMK Teladan - Bot Otomatis",
            'buttonText' => "MENU PILIHAN",
            'sections' => [
                [
                    'title' => 'Informasi Harian',
                    'rows' => [
                        ['title' => 'Cek Absensi Hari Ini', 'rowId' => 'CEK_ABSENSI_HARI_INI', 'description' => 'Balas 1'],
                        ['title' => 'Jadwal Pelajaran', 'rowId' => 'CEK_JADWAL', 'description' => 'Balas 2'],
                        ['title' => 'Info SPP', 'rowId' => 'CEK_SPP', 'description' => 'Balas 3']
                    ]
                ],
                [
                    'title' => 'Laporan Rekapitulasi',
                    'rows' => [
                        ['title' => 'Rekap Mingguan', 'rowId' => 'REKAP_MINGGU', 'description' => 'Balas 4'],
                        ['title' => 'Rekap Bulanan', 'rowId' => 'REKAP_BULAN', 'description' => 'Balas 5'],
                        ['title' => 'Rekap Semester', 'rowId' => 'REKAP_SEMESTER', 'description' => 'Balas 6']
                    ]
                ]
            ]
        ];

        $this->postToNode($payload);
    }

    /**
     * Cek Absensi Lengkap (Gerbang, Jadwal & Pembelajaran) - Harian
     */
    private function cekAbsensiHarian($phone, $student, $sessionId)
    {
        $today = Carbon::today();
        $hariIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

        // --- 1. KEHADIRAN GERBANG ---
        $gate = DailyAttendance::where('student_id', $student->id)
                                ->whereDate('created_at', $today)
                                ->first();

        if ($gate) {
            $waktu = $gate->arrival_time ?? $gate->created_at;
            $jamMasuk = Carbon::parse($waktu)->format('H:i');
            $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
        } else {
            $statusGerbang = "BELUM ABSEN / ALPA";
        }

        // --- 2. JADWAL PELAJARAN (Hari Ini) ---
        $listJadwal = "";
        try {
            $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
                                    ->where('classroom_id', $student->classroom_id)
                                    ->where('day', $namaHari)
                                    ->orderBy('start_time', 'asc')
                                    ->get();

            if ($schedules->count() > 0) {
                foreach ($schedules as $sched) {
                    $mapel = $sched->subject->name ?? 'Mapel';
                    $guru = $sched->teacher->name ?? '-';
                    $jamMulai = substr($sched->start_time, 0, 5);
                    $jamSelesai = substr($sched->end_time, 0, 5);
                    $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
                }
            } else {
                $listJadwal = "_Tidak ada jadwal pelajaran_";
            }
        } catch (\Throwable $e) {
            $listJadwal = "_Data jadwal belum tersedia_";
        }

        // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
        $listPembelajaran = "";
        try {
            $lessons = \App\Models\Attendance::with('subject') 
                                    ->where('student_id', $student->id)
                                    ->whereDate('created_at', $today)
                                    ->orderBy('created_at', 'asc')
                                    ->get();

            if ($lessons->count() > 0) {
                foreach ($lessons as $lesson) {
                    $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
                    $statusLesson = strtoupper($lesson->status);
                    $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
                    $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
                }
            } else {
                $listPembelajaran = "_Belum ada data pembelajaran masuk_";
            }
        } catch (\Throwable $e) {
            $listPembelajaran = "_Data pembelajaran tidak tersedia_";
        }

        // --- SUSUN PESAN FINAL ---
        $message = "📊 *LAPORAN HARIAN SISWA*\n" .
                   "Nama: *{$student->name}*\n" .
                   "Hari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n" .
                   "🏫 *KEHADIRAN GERBANG*\n" .
                   "Status: *{$statusGerbang}*\n\n" .
                   "📅 *JADWAL HARI INI*\n" .
                   $listJadwal . "\n" .
                   "📚 *KEHADIRAN PEMBELAJARAN*\n" .
                   $listPembelajaran;

        $this->sendText($phone, $message, $sessionId);
    }

    /**
     * Hitung Rekapitulasi Absensi (Mingguan/Bulanan/Semester)
     */
    private function cekRekapAbsensi($phone, $student, $sessionId, $period)
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now();
        $label = "";

        // Tentukan Rentang Waktu
        if ($period == 'weekly') {
            $startDate = Carbon::now()->subDays(7);
            $label = "MINGGU INI (7 Hari Terakhir)";
        } elseif ($period == 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
        } elseif ($period == 'semester') {
            // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
            $month = Carbon::now()->month;
            if ($month >= 7) {
                $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
                $label = "SEMESTER GANJIL";
            } else {
                $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
                $label = "SEMESTER GENAP";
            }
        }

        // 1. REKAP GERBANG (Hitung jumlah Status)
        // Output: [HADIR => 5, SAKIT => 1, ...]
        $rekapGerbang = DailyAttendance::where('student_id', $student->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->selectRaw('status, count(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status')
                        ->toArray();

        // Format Text Gerbang
        $txtGerbang = "";
        $totalKehadiran = 0;
        if (empty($rekapGerbang)) {
            $txtGerbang = "_Belum ada data absensi_";
        } else {
            foreach ($rekapGerbang as $status => $total) {
                $statusUpper = strtoupper($status);
                $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
                $totalKehadiran += $total;
            }
            $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
        }

        // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
        try {
            $totalMapel = Attendance::where('student_id', $student->id)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->where('status', 'hadir') // Asumsi hanya menghitung kehadiran
                            ->count();
            
            $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
        } catch (\Throwable $e) {
            $txtMapel = "-";
        }

        // SUSUN PESAN
        $message = "📈 *LAPORAN REKAPITULASI*\n" .
                   "Nama: *{$student->name}*\n" .
                   "Periode: {$label}\n\n" .
                   "🏫 *ABSENSI GERBANG*\n" .
                   $txtGerbang . "\n\n" .
                   "📚 *ABSENSI PEMBELAJARAN*\n" .
                   $txtMapel . "\n\n" .
                   "_Balas MENU untuk kembali ke menu utama._";

        $this->sendText($phone, $message, $sessionId);
    }

    /**
     * Logika Cek Jadwal
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
     * Logika Cek SPP
     */
    private function cekSPP($phone, $student, $sessionId)
    {
        $message = "Info Keuangan Siswa:\n\n" .
                   "Nama: *{$student->name}*\n" .
                   "Bulan: " . date('F Y') . "\n" .
                   "Status SPP: *LUNAS*\n" .
                   "Tunggakan Lain: Rp 0,-";

        $this->sendText($phone, $message, $sessionId);
    }

    /**
     * Helper kirim pesan teks
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
     * Wrapper Http Request
     */
    private function postToNode($payload)
    {
        try {
            $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
            if ($response->failed()) {
                Log::error("Gagal kirim ke Node.js: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Koneksi Node.js Refused: " . $e->getMessage());
            throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan."); 
        }
    }
}