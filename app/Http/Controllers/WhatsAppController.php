<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Classroom;
use App\Models\Student;
use App\Jobs\SendWhatsappJob;

class WhatsAppController extends Controller
{
    /**
     * WEB: Tampilkan Halaman Test WA (Dashboard Admin)
     * Route: GET /whatsapp/test
     */
    public function index()
    {
        return view('whatsapp.send');
    }

    /**
     * WEB: Proses Kirim Pesan Manual (Redirect Back)
     * Route: POST /whatsapp/send
     */
    public function store(Request $request)
    {
        $request->validate([
            'target' => 'required|numeric',
            'message' => 'required|string',
        ]);

        // Panggil fungsi private pengirim pesan (Langsung tanpa Queue untuk testing)
        $result = self::sendMessageToBaileys($request->target, $request->message);

        if ($result['status'] == 'success') {
            return redirect()->back()->with('success', 'Pesan berhasil dikirim via WhatsApp!');
        } else {
            return redirect()->back()->with('error', 'Gagal kirim: ' . $result['message']);
        }
    }

    /**
     * [BARU] Halaman Form Broadcast Per Kelas
     * Route: GET /whatsapp/broadcast
     */
    public function broadcast()
    {
        // Ambil data kelas untuk dropdown
        $classrooms = Classroom::orderBy('name')->get();
        return view('whatsapp.broadcast', compact('classrooms'));
    }

    /**
     * [BARU] Proses Kirim Broadcast Massal
     * Route: POST /whatsapp/broadcast
     */
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'message'      => 'required|string',
            'attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // Max 5MB
        ]);

        // 1. Ambil Siswa di Kelas Tersebut
        $students = Student::where('classroom_id', $request->classroom_id)
                           ->whereNotNull('phone')
                           ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa dengan nomor HP di kelas ini.');
        }

        // 2. Handle File Upload (Jika ada lampiran)
        $mediaUrl = null;
        $type = 'text';
        $fileName = null;
        $mimeType = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();

            // Simpan di storage public agar bisa diakses Node.js via URL
            // Pastikan Anda sudah menjalankan: php artisan storage:link
            $path = $file->store('broadcasts', 'public');
            $mediaUrl = asset('storage/' . $path);

            // Tentukan tipe pesan (image atau document)
            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } else {
                $type = 'document';
            }
        }

        // 3. Dispatch Job untuk Setiap Siswa
        $count = 0;
        foreach ($students as $student) {
            // Bersihkan nomor HP
            if (empty($student->phone)) continue;

            // Kirim ke Antrian (Queue)
            SendWhatsappJob::dispatch(
                $student->phone,
                $request->message,
                $type,
                $mediaUrl,
                $fileName,
                $mimeType
            );
            $count++;
        }

        return back()->with('success', "Pesan sedang dikirim ke $count orang tua siswa di latar belakang.");
    }

    /**
     * API: Proses Kirim Pesan via API (Return JSON)
     * Endpoint: POST /api/whatsapp/send
     */
    public function sendApi(Request $request)
    {
        // Validasi input API
        $validator = Validator::make($request->all(), [
            'target' => 'required|numeric',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Panggil fungsi private pengirim pesan
        $result = self::sendMessageToBaileys($request->target, $request->message);

        // Return JSON Response
        if ($result['status'] == 'success') {
            return response()->json([
                'status' => 'success',
                'message' => 'Pesan berhasil dikirim',
                'data' => $result['data'] ?? null
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * PRIVATE STATIC: Fungsi Reusable untuk Kirim ke Node.js (Baileys)
     * Digunakan oleh method store() dan sendApi() untuk pengiriman langsung (bukan queue)
     */
    private static function sendMessageToBaileys($target, $message)
    {
        try {
            // Tembak ke Service Node.js (Port 3000)
            // Timeout 5 detik agar tidak loading selamanya jika bot mati
            $response = Http::timeout(5)->post('http://localhost:3000/send-message', [
                'number' => $target,
                'message' => $message,
                'type' => 'text'
            ]);

            // Cek status dari Node.js
            if ($response->successful() && isset($response['status']) && $response['status'] == 'success') {
                return ['status' => 'success', 'data' => $response->json()];
            } else {
                return ['status' => 'error', 'message' => $response['message'] ?? 'Bot tidak merespon atau error internal'];
            }

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Koneksi ke Bot Node.js Gagal (Port 3000). Pastikan service "node index.js" berjalan. Error: ' . $e->getMessage()];
        }
    }
}
