<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 2. BUAT USER (ADMIN & GURU)
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $guru = User::create([
            'name' => 'Pak Guru Gatech',
            'email' => 'guru@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $guru->assignRole('guru');

        $piket = User::create([
            'name' => 'Pak Piket Gatech',
            'email' => 'piket@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $piket->assignRole('piket');

        $kelasTITL = Classroom::where('name', 'XII TITL 1')->first();
        // Siswa B: Masuk kelas TKJ (Untuk ngetes fitur "Salah Kelas")
        Student::create([
            'nis' => '67890',
            'name' => 'Budi TKJ',
            'classroom_id' => $kelasTITL->id
        ]);
    }
}
