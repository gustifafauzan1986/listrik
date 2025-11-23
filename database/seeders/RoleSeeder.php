<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permission (Hak Akses Spesifik)
        Permission::create(['name' => 'master_data']); // Edit siswa, jadwal, user
        Permission::create(['name' => 'scan_attendance']); // Buka scanner
        Permission::create(['name' => 'view_report']); // Lihat laporan

        // 3. Buat Role & Assign Permission
        
        // Role: GURU
        $roleGuru = Role::create(['name' => 'guru']);
        $roleGuru->givePermissionTo(['scan_attendance', 'view_report']);

        // Role: ADMIN
        $roleAdmin = Role::create(['name' => 'admin']);
        // Admin punya semua permission
        $roleAdmin->givePermissionTo(Permission::all());

        // 4. Buat User Demo (Opsional, jika database kosong)
        
        // Akun Admin
        $admin = User::create([
            'id' => Str::uuid(),
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => bcrypt('password')
        ]);
        $admin->assignRole('admin');

        // Akun Guru
        $guru = User::create([
            'id' => Str::uuid(),
            'name' => 'Pak Budi',
            'email' => 'guru@sekolah.com',
            'password' => bcrypt('password')
        ]);
        $guru->assignRole('guru');

    }
}
