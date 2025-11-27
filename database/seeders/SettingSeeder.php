<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'school_name'    => 'SMK NEGERI 1 CONTOH',
            'school_address' => 'Jl. Pendidikan No. 123, Jakarta Selatan',
            'school_phone'   => '(021) 555-5555',
            'school_web'     => 'www.sekolah.sch.id',
            'school_email'   => 'info@sekolah.sch.id',
        ];

        foreach ($data as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }
    }
}
