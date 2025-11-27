<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil semua setting dan ubah jadi array key => value
        // Hasilnya: ['school_name' => 'SMK...', 'school_phone' => '...']
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Validasi (Opsional)
        $request->validate([
            'school_name' => 'required|string',
        ]);

        // Loop semua input dan simpan ke database
        // Kita abaikan token CSRF dan method PUT
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan Sekolah Berhasil Disimpan!');
    }
}
