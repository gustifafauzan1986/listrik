<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Tambahkan ini

class StudentImport implements ToModel, WithHeadingRow
{

 private $duplicates = [];
    private $successCount = 0;

    public function model(array $row)
    {
        // Cek Duplikat Manual (contoh)
        if (Student::where('nis', $row['nis'])->exists()) {
            $this->duplicates[] = $row['nama'] . ' (' . $row['nis'] . ')';
            return null; // Skip penyimpanan
        }

        $this->successCount++;
        return new Student([
            'nis'  => $row['nis'],
            'nama' => $row['nama'],
            // ...
        ]);
    }

    public function getDuplicates() { return $this->duplicates; }
    public function getSuccessCount() { return $this->successCount; }


}
