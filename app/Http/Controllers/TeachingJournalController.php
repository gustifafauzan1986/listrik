<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeachingJournal;
use Illuminate\Support\Facades\Storage;

class TeachingJournalController extends Controller
{
    // Simpan atau Update Jurnal
    public function store(Request $request)
    {
        try {
            $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'topic'       => 'required|string|max:255',
                'activity'    => 'nullable|string',
            ]);

            // Cek apakah jurnal untuk jadwal ini sudah ada (update) atau belum (create)
            $journal = TeachingJournal::updateOrCreate(
                ['schedule_id' => $request->schedule_id],
                [
                    'topic'    => $request->topic,
                    'activity' => $request->activity,
                    'notes'    => $request->notes,
                ]
            );

            // Handle Upload Foto (Jika ada)
            if ($request->hasFile('photo')) {
                // Hapus foto lama jika ada
                if ($journal->photo_evidence) {
                    Storage::disk('public')->delete($journal->photo_evidence);
                }

                $path = $request->file('photo')->store('journals', 'public');
                $journal->update(['photo_evidence' => $path]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Jurnal pembelajaran berhasil disimpan!',
                'data' => $journal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan jurnal: ' . $e->getMessage()
            ], 500);
        }
    }

    // Ambil data jurnal (untuk ditampilkan di modal saat dibuka)
    public function show($scheduleId)
    {
        $journal = TeachingJournal::where('schedule_id', $scheduleId)->first();

        return response()->json([
            'status' => 'success',
            'data' => $journal
        ]);
    }
}
