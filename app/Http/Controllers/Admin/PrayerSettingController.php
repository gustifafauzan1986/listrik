<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class PrayerSettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Lokasi Masjid
     */
    public function index()
    {
        // Ambil data setting atau gunakan default
        $lat    = Setting::value('masjid_lat', -0.305123);
        $lng    = Setting::value('masjid_lng', 100.369456);
        $radius = Setting::value('masjid_radius', 50);

        return view('admin.prayer.settings', compact('lat', 'lng', 'radius'));
    }

    /**
     * Simpan Pengaturan Lokasi
     */
    public function update(Request $request)
    {
        $request->validate([
            'masjid_lat'    => 'required|numeric',
            'masjid_lng'    => 'required|numeric',
            'masjid_radius' => 'required|numeric|min:5|max:500', // Minimal 5m, Maksimal 500m
        ]);

        // Simpan ke tabel settings
        // Asumsi Anda punya helper/model Setting::set('key', 'value')
        // Jika belum, gunakan updateOrCreate manual

        Setting::updateOrCreate(['key' => 'masjid_lat'], ['value' => $request->masjid_lat]);
        Setting::updateOrCreate(['key' => 'masjid_lng'], ['value' => $request->masjid_lng]);
        Setting::updateOrCreate(['key' => 'masjid_radius'], ['value' => $request->masjid_radius]);

        return redirect()->back()->with('success', 'Lokasi Masjid dan Radius Absensi berhasil diperbarui!');
    }
}
