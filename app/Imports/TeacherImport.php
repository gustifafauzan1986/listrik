<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeacherImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // 1. Validasi Dasar
        if (!isset($row['email']) || !isset($row['nama'])) {
            return null;
        }

        // 2. Buat atau Update Akun Login (Tabel Users)
        $user = User::updateOrCreate(
            ['email' => $row['email']],
            [
                'name'     => $row['nama'],
                'password' => Hash::make($row['password'] ?? 'guru123'), // Default password jika kosong
            ]
        );

        // 3. Pastikan Role-nya Guru
        $user->assignRole('guru');

        // 4. Simpan Data Profil (Tabel Teachers)
        // Kita gunakan updateOrCreate berdasarkan user_id agar tidak duplikat
        return Teacher::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nip'             => $row['nip'] ?? null,
                'gender'          => $row['jk'] ?? null, // L/P
                'phone'           => $row['hp'] ?? null,
                'place_of_birth'  => $row['tempat_lahir'] ?? null,
                'address'         => $row['alamat'] ?? null,
                'education_level' => $row['pendidikan'] ?? null, // S1/S2
            ]
        );
    }
}