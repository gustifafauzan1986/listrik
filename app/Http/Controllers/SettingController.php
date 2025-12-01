<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan
     */
    public function index()
    {
        // Mengambil semua data settings menjadi array key => value
        // Contoh: ['school_name' => 'SMK 1', 'logo_left' => 'settings/logo.png']
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    /**
     * Proses Simpan / Update Pengaturan
     */
    public function update(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'school_name' => 'required|string|max:255',
            'logo_left'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'logo_right'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Handle Upload Logo Kiri
        if ($request->hasFile('logo_left')) {
            // Hapus file lama jika ada
            $oldLogo = Setting::value('logo_left');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Simpan file baru
            $pathLeft = $request->file('logo_left')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'logo_left'], ['value' => $pathLeft]);
        }

        // 3. Handle Upload Logo Kanan
        if ($request->hasFile('logo_right')) {
            $oldLogo = Setting::value('logo_right');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $pathRight = $request->file('logo_right')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'logo_right'], ['value' => $pathRight]);
        }

        // 4. Simpan Data Teks Lainnya (dinamis)
        // Kita ambil semua input kecuali token, method, dan file
        $data = $request->except(['_token', '_method', 'logo_left', 'logo_right']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan Sekolah & Logo Berhasil Disimpan!');
    }
}
