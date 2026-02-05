<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Setting;

class PrayerServerSyncController extends Controller
{
    /**
     * Endpoint untuk memberikan data ke server lain
     */
    public function exportData(Request $request)
    {
        // 1. Validasi Security Sederhana
        $serverKey = Setting::value('server_sync_key');

        // Jika belum disetting di database, tolak
        if (!$serverKey) {
            return response()->json(['status' => 'error', 'message' => 'Server Sync Key not configured on source server.'], 403);
        }

        // Cek apakah key yang dikirim client sesuai
        if ($request->header('X-Server-Key') !== $serverKey && $request->input('key') !== $serverKey) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Invalid Key.'], 401);
        }

        // 2. Ambil Parameter Tanggal
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-t'));

        // 3. Ambil Data dengan Relasi Siswa (PENTING: Kita butuh NIS/NISN untuk mencocokkan siswa antar server)
        $attendances = PrayerAttendance::with('student:id,nis,name')
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function($item) {
                return [
                    'nis'           => $item->student->nis ?? null, // Kunci pencocokan
                    'student_name'  => $item->student->name ?? null,
                    'date'          => $item->date->format('Y-m-d'),
                    'prayer_name'   => $item->prayer_name,
                    'check_in_time' => $item->check_in_time,
                    'status'        => $item->status,
                    'latitude'      => $item->latitude,
                    'longitude'     => $item->longitude,
                ];
            });

        return response()->json([
            'status' => 'success',
            'count' => $attendances->count(),
            'data' => $attendances
        ]);
    }
}
