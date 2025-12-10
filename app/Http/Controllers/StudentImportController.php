<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentImport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log; // Untuk logging error

class StudentImportController extends Controller
{
    public function index()
    {
        return view('students.import');
    }

    public function store(Request $request)
    {
        // 1. Validasi File (tambahkan max size 5MB)
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            // 2. Buat Instance terlebih dahulu (PENTING)
            // Agar kita bisa mengambil data $duplicates & $successCount setelah import selesai
            $import = new StudentImport();

            // 3. Jalankan Import menggunakan instance $import
            Excel::import($import, $request->file('file'));

            // 4. Jalankan Command Artisan (Sesuai request Anda)
            // Ini akan membuat user login berdasarkan data siswa yang baru masuk
            Artisan::call('siswa:generate-users');

            // 5. Ambil Data Statistik dari Import Class
            $duplicates   = $import->getDuplicates();
            $successCount = $import->getSuccessCount();

            // 6. Logika Pesan SweetAlert
            if (count($duplicates) > 0) {
                // --- KASUS: ADA DUPLIKAT ---

                // Susun HTML list untuk alert
                $duplicateList = '<ul style="text-align: left; font-size: 0.9em; margin-bottom:0;">';
                foreach (array_slice($duplicates, 0, 5) as $dup) {
                    $duplicateList .= "<li>- " . e($dup) . "</li>"; // e() untuk security
                }
                if (count($duplicates) > 5) {
                    $sisa = count($duplicates) - 5;
                    $duplicateList .= "<li>... dan <b>{$sisa}</b> lainnya.</li>";
                }
                $duplicateList .= '</ul>';

                // Tentukan pesan (Warning jika sebagian masuk, Error jika gagal semua)
                $isPartial = $successCount > 0;

                $notification = [
                    'icon'  => $isPartial ? 'warning' : 'error',
                    'title' => 'Import Selesai dengan Catatan',
                    'html'  => $isPartial
                        ? "<b>{$successCount}</b> siswa berhasil disimpan.<br><br>Namun <b>" . count($duplicates) . "</b> data dilewati karena NIS duplikat:<br>{$duplicateList}"
                        : "Semua data (<b>" . count($duplicates) . "</b>) gagal disimpan karena NIS duplikat.<br>{$duplicateList}"
                ];

            } else {
                // --- KASUS: SUKSES 100% ---
                if ($successCount > 0) {
                    $notification = [
                        'icon'  => 'success',
                        'title' => 'Import Berhasil!',
                        'text'  => "Total $successCount data siswa berhasil diimport dan akun pengguna telah digenerate."
                    ];
                } else {
                    // File kosong
                    $notification = [
                        'icon'  => 'info',
                        'title' => 'Tidak Ada Data',
                        'text'  => 'File Excel tampak kosong atau tidak ada data valid yang diproses.'
                    ];
                }
            }

            // Kembalikan ke halaman sebelumnya dengan session 'swal'
            return redirect()->back()->with('swal', $notification);

        } catch (\Exception $e) {
            // 7. Error Handling (Jika format Excel hancur / Error Server)
            Log::error('Import Siswa Error: ' . $e->getMessage());

            $notification = [
                'icon'  => 'error',
                'title' => 'Gagal Memproses File',
                'text'  => 'Terjadi kesalahan sistem. Pastikan format header Excel sesuai (nis, nama_siswa, kelas). Error: ' . $e->getMessage()
            ];

            return redirect()->back()->with('swal', $notification);
        }
    }
}
