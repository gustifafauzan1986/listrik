<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrayerServerSyncController extends Controller
{
    /**
     * Endpoint untuk memberikan data ke server lain
     */
    // public function exportData(Request $request)
    // {
    //     // 1. Validasi Security Sederhana
    //     $serverKey = Setting::value('server_sync_key');

    //     // Jika belum disetting di database, tolak
    //     if (!$serverKey) {
    //         return response()->json(['status' => 'error', 'message' => 'Server Sync Key not configured on source server.'], 403);
    //     }

    //     // Cek apakah key yang dikirim client sesuai
    //     if ($request->header('X-Server-Key') !== $serverKey && $request->input('key') !== $serverKey) {
    //         return response()->json(['status' => 'error', 'message' => 'Unauthorized. Invalid Key.'], 401);
    //     }

    //     // 2. Ambil Parameter Tanggal
    //     $startDate = $request->input('start_date', date('Y-m-01'));
    //     $endDate   = $request->input('end_date', date('Y-m-t'));

    //     // 3. Ambil Data dengan Relasi Siswa (PENTING: Kita butuh NIS/NISN untuk mencocokkan siswa antar server)
    //     $attendances = PrayerAttendance::with('student:id,nis,name')
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->get()
    //         ->map(function($item) {
    //             return [
    //                 'nis'           => $item->student->nis ?? null, // Kunci pencocokan
    //                 'student_name'  => $item->student->name ?? null,
    //                 'date'          => $item->date->format('Y-m-d'),
    //                 'prayer_name'   => $item->prayer_name,
    //                 'check_in_time' => $item->check_in_time,
    //                 'status'        => $item->status,
    //                 'latitude'      => $item->latitude,
    //                 'longitude'     => $item->longitude,
    //             ];
    //         });

    //     return response()->json([
    //         'status' => 'success',
    //         'count' => $attendances->count(),
    //         'data' => $attendances
    //     ]);
    // }

     /**
     * Export data absensi untuk ditarik oleh Server B
     */
    // public function exportData(Request $request)
    // {
    //     // 1. Ambil Key yang valid dari database Server A
    //     $validKey = Setting::where('key', 'server_sync_key')->value('value');

    //     // 2. Ambil Key dari Request (Bisa dari Header atau Query Parameter)
    //     $providedKey = $request->header('X-Server-Key') ?? $request->query('key');

    //     // 3. Validasi Key
    //     if (!$validKey || $providedKey !== $validKey) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Unauthorized: API Key tidak valid atau belum diatur di Server A.'
    //         ], 401);
    //     }

    //     // 4. Validasi Parameter Tanggal
    //     $startDate = $request->query('start_date');
    //     $endDate   = $request->query('end_date');

    //     if (!$startDate || !$endDate) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Parameter start_date dan end_date diperlukan.'
    //         ], 400);
    //     }

    //     try {
    //         // 5. Ambil data absensi dengan Join ke tabel Students untuk mendapatkan NIS
    //         // NIS digunakan sebagai identifier unik antar server
    //         $data = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances::student_id', '=', 'students.id')
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'total'  => $data->count(),
    //             'data'   => $data
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Internal Server Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function exportData(Request $request)
    // {
    //     // Validasi API Key sederhana untuk keamanan antar server
    //     $apiKey = $request->header('X-Sync-Key') ?? $request->query('key');
    //     if ($apiKey !== config('app.sync_token', 'token_rahasia_anda')) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $startDate = $request->query('start_date');
    //     $endDate = $request->query('end_date');

    //     // Mengambil data berdasarkan rentang tanggal
    //     $query = PrayerAttendance::query();

    //     if ($startDate && $endDate) {
    //         $query->whereBetween('created_at', [$startDate, $endDate]);
    //     }

    //     $data = $query->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'server_time' => now()->toDateTimeString(),
    //         'total_records' => $data->count(),
    //         'data' => $data
    //     ], 200);
    // }

    public function exportData(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Gunakan Query Builder untuk kontrol join yang lebih presisi
            $query = DB::table('prayer_attendances')
                ->join('students', 'prayer_attendances.student_id', '=', 'students.id') // Perbaikan: gunakan titik (.)
                ->select(
                    'students.nis',
                    'prayer_attendances.date',
                    'prayer_attendances.prayer_name',
                    'prayer_attendances.check_in_time',
                    'prayer_attendances.status',
                    'prayer_attendances.latitude',
                    'prayer_attendances.longitude'
                );

            // Filter tanggal jika tersedia
            if ($startDate && $endDate) {
                $query->whereBetween('prayer_attendances.date', [$startDate, $endDate]);
            }

            $data = $query->get();

            return response()->json([
                'status' => 'success',
                'total_records' => $data->count(),
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            // Log error untuk memudahkan debugging di sisi server
            Log::error('Export Sync Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

     /**
     * Export Terpadu: Sholat, Gerbang, dan Pembelajaran
     */
    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang (daily_attendances)
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran (attendances)
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             // Sertakan UUID schedule dan subject untuk referensi di server tujuan
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => [
    //                     'total' => $prayerData->count(),
    //                     'data' => $prayerData
    //                 ],
    //                 'gate' => [
    //                     'total' => $gateData->count(),
    //                     'data' => $gateData
    //                 ],
    //                 'learning' => [
    //                     'total' => $learningData->count(),
    //                     'data' => $learningData
    //                 ]
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function exportAll(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if (!$startDate || !$endDate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
                ], 400);
            }

            // 1. Ambil Data Absensi Sholat
            $prayerData = DB::table('prayer_attendances')
                ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
                ->select(
                    'students.nis',
                    'prayer_attendances.date',
                    'prayer_attendances.prayer_name',
                    'prayer_attendances.check_in_time',
                    'prayer_attendances.status',
                    'prayer_attendances.latitude',
                    'prayer_attendances.longitude'
                )
                ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
                ->get();

            // 2. Ambil Data Absensi Gerbang (daily_attendances)
            $gateData = DB::table('daily_attendances')
                ->join('students', 'daily_attendances.student_id', '=', 'students.id')
                ->select(
                    'students.nis',
                    'daily_attendances.date',
                    'daily_attendances.arrival_time',
                    'daily_attendances.departure_time',
                    'daily_attendances.status',
                    'daily_attendances.recorded_by'
                )
                ->whereBetween('daily_attendances.date', [$startDate, $endDate])
                ->get();

            // 3. Ambil Data Absensi Pembelajaran (attendances)
            $learningData = DB::table('attendances')
                ->join('students', 'attendances.student_id', '=', 'students.id')
                // Sertakan UUID schedule dan subject untuk referensi di server tujuan
                ->select(
                    'students.nis',
                    'attendances.schedule_id',
                    'attendances.subject_id',
                    'attendances.date',
                    'attendances.check_in_time',
                    'attendances.status',
                    'attendances.recorded_by'
                )
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->get();

            // 4. Ambil Data Jurnal Guru (journals)
            // Jurnal tidak perlu join ke students, tapi bergantung pada schedule_id
            $journalData = DB::table('teaching_journals')
                ->select(
                    'teaching_journals.schedule_id',
                    'teaching_journals.date',
                    'teaching_journals.topic',
                    'teaching_journals.activity',
                    'teaching_journals.attendance_summary',
                    'teaching_journals.absent_details'
                )
                ->whereBetween('teaching_journals.date', [$startDate, $endDate])
                ->get();

            return response()->json([
                'status' => 'success',
                'filter' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'results' => [
                    'prayer' => [
                        'total' => $prayerData->count(),
                        'data' => $prayerData
                    ],
                    'gate' => [
                        'total' => $gateData->count(),
                        'data' => $gateData
                    ],
                    'learning' => [
                        'total' => $learningData->count(),
                        'data' => $learningData
                    ],
                    'journal' => [
                        'total' => $journalData->count(),
                        'data' => $journalData
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Full Sync Export Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }
}
