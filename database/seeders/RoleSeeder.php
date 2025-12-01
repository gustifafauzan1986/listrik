<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Classroom;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. RESET CACHE PERMISSION
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // 1. SETUP ROLE & PERMISSION
        Permission::create(['name' => 'master_data']);
        Permission::create(['name' => 'scan_attendance']);
        Permission::create(['name' => 'view_report']);
        Permission::create(['name' => 'daily_attendance']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleGuru = Role::create(['name' => 'guru']);
        $roleGuru->givePermissionTo(['scan_attendance', 'view_report']);

        $rolePiket = Role::create(['name' => 'piket']);
        $rolePiket->givePermissionTo(['daily_attendance']);

        $roleSiswa = Role::create(['name' => 'siswa']); // Role untuk login siswa nanti


        //$this->call(AddPiketRoleSeeder::class);
        //$this->call(DailyAttendancePermissionSeeder::class);

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


        // 3. BUAT DATA KELAS (CLASSROOMS)
        // Kita simpan ke variabel agar ID-nya bisa dipakai di bawah
        // $kelasRPL = Classroom::create(['name' => 'XII RPL 1']);
        // $kelasTKJ = Classroom::create(['name' => 'XII TKJ 2']);

        // (Opsional) Panggil ClassroomSeeder jika ingin generate kelas massal
        // $this->call(ClassroomSeeder::class);


        // 4. BUAT DATA SISWA (LINK KE KELAS RPL)
        // Siswa A: Masuk kelas RPL (Cocok dengan jadwal nanti)
        // Student::create([
        //     'nis' => '12345',
        //     'name' => 'Ahmad RPL',
        //     'classroom_id' => $kelasRPL->id
        // ]);
        $kelasTITL = \App\Models\Classroom::where('name', 'XII TITL 1')->first();
        // Siswa B: Masuk kelas TKJ (Untuk ngetes fitur "Salah Kelas")
        \App\Models\Student::create([
            'nis' => '67890',
            'name' => 'Budi TKJ',
            'classroom_id' => $kelasTITL->id
        ]);
        // 5. BUAT JADWAL PELAJARAN (LINK KE KELAS RPL & GURU BUDI)
        //Jadwal 1: Pemrograman Web (Sabtu) untuk kelas RPL
        Schedule::create([
            'teacher_id' => $guru->id,       // Milik Pak Budi
            'classroom_id' => $kelasTITL->id, // Untuk Kelas XII RPL 1
            'subject_name' => 'Pemrograman Web',
            'day' => 'Sabtu',                // Sesuaikan dengan hari testing Anda
            'start_time' => '07:00:00',
            'end_time' => '23:59:00',
        ]);

        // Jadwal 2: Jaringan Dasar (Sabtu) untuk kelas TKJ
        Schedule::create([
            'teacher_id' => $guru->id,
            'classroom_id' => $kelasTITL->id, // Untuk Kelas XII TKJ 2
            'subject_name' => 'Jaringan Dasar',
            'day' => 'Sabtu',
            'start_time' => '07:00:00',
            'end_time' => '23:59:00',
        ]);

        $this->command->info('Seeder Selesai! User, Kelas, Siswa, dan Jadwal sudah terhubung.');

    }
}
