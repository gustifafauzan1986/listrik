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
    public function model(array $row)
    {
        // Validasi sederhana: Jika baris kosong/tidak ada NIS, skip
        if (!isset($row['nis']) || !isset($row['kelas'])) {
            return null;
        }

        // 1. LOGIKA PENCARIAN KELAS
        // Kita cari kelas berdasarkan nama yang ada di Excel (Kolom 'kelas')
        // Gunakan firstOrCreate: Jika kelas belum ada, sistem otomatis membuatnya.
        $classroom = Classroom::firstOrCreate(
            ['name' => strtoupper(trim($row['kelas']))] 
        );
        // Pastikan NIS unik, jika ada update, jika tidak buat baru
        return Student::updateOrCreate(
            ['nis' => $row['nis']], // Kunci pencarian (Cek NIS)
            [
                'name'       => $row['nama_siswa'], // Sesuaikan dengan header Excel
                'classroom_id' => $classroom->id, // <--- Masukkan UUID disini
            ]
        );
    }
}
