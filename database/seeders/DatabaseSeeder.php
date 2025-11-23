<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run()
{
    // Buat User Guru (ID 1)
    // \App\Models\User::factory()->create([
    //     'name' => 'Pak Guru Budi',
    //     'email' => 'guru@sekolah.com',
    //     'password' => bcrypt('password'),
    // ]);

    // Buat Siswa
    // \App\Models\Student::create([
    //     'id' => Str::uuid(),
    //     'nis' => '12345', // Scan QR kode "12345" nanti
    //     'name' => 'Ahmad Siswa',
    //     'class_name' => 'XII RPL 1'
    // ]);

    // Buat Jadwal (Sesuaikan jam dengan waktu Anda mengetes sekarang!)
    \App\Models\Schedule::create([
        'id' => Str::uuid(),
        'teacher_id' => '490a498f-6b62-4168-9219-178bfeda35c3',
        //'teacher_id' => 1,
        'subject_name' => 'Pemrograman Web',
        'day' => 'Sabtu', // Ganti sesuai hari Anda test
        'start_time' => '07:00:00',
        'end_time' => '23:59:00', // Set panjang biar gampang test
    ]);
}
}
