<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Tambahkan ini

class StudentImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */


    // Variabel untuk menampung NIS yang ganda
    private $duplicates = [];
    private $successCount = 0;

    public function model(array $row)
    {
         // Validasi dasar
        if (!isset($row['nis']) || !isset($row['kelas'])) {
            return null;
        }

        // 1. CEK DUPLIKASI NIS
        // Jika NIS sudah ada di database, masukkan ke list duplicates & SKIP
        if (Student::where('nis', $row['nis'])->exists()) {
            $this->duplicates[] = $row['nis'] . " - " . ($row['nama_siswa'] ?? 'Tanpa Nama');
            return null; // Jangan simpan
        }

        // 2. CARI / BUAT KELAS
        $classroom = Classroom::firstOrCreate(
            ['name' => strtoupper(trim($row['kelas']))]
        );

        // 3. SIMPAN SISWA BARU
        $this->successCount++;
        return new Student([
            'nis'          => $row['nis'],
            'name'         => $row['nama_siswa'],
            'classroom_id' => $classroom->id,
            // Tambahkan Nomor HP (pastikan header di excel 'no_hp')
            'phone'        => $row['no_hp'] ?? null,
        ]);
    }

    // Fungsi getter untuk mengambil data duplikat dari Controller
    public function getDuplicates()
    {
        return $this->duplicates;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
