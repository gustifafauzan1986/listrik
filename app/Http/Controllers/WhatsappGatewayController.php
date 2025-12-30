<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappGateway;
use Illuminate\Support\Facades\Http;

class WhatsappGatewayController extends Controller
{
    // List Semua Gateway
    public function index()
    {
        // Sinkronisasi status dari Node.js ke DB saat halaman dibuka
        try {
            $response = Http::timeout(3)->get('http://localhost:3000/sessions');
            if ($response->successful()) {
                $sessions = $response->json();
                foreach ($sessions as $s) {
                    WhatsappGateway::where('session_id', $s['session_id'])->update([
                        'status' => $s['status'],
                        'number' => $s['phone'] ?? null
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Node.js mati atau tidak bisa dijangkau, abaikan agar halaman tetap loading
        }

        $gateways = WhatsappGateway::all();
        return view('admin.whatsapp.index', compact('gateways'));
    }

    // Tambah Gateway Baru
    public function store(Request $request)
    {
        // Tidak ada validasi request karena semua data digenerate otomatis
        
        $count = WhatsappGateway::count() + 1;
        $sessionId = 'gateway_' . $count . '_' . time();

        // Register ke Node.js
        try {
            Http::post('http://localhost:3000/session/start', ['session_id' => $sessionId]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal koneksi ke WA Service Node.js. Pastikan service berjalan.');
        }

        WhatsappGateway::create([
            'session_id' => $sessionId,
            'name' => 'Gateway #' . $count,
            'status' => 'scan_needed'
        ]);

        return back()->with('success', 'Gateway baru ditambahkan. Silakan Scan QR.');
    }

    // Hapus Gateway
    public function destroy($id)
    {
        $gateway = WhatsappGateway::findOrFail($id);
        
        // Logout di Node.js
        try {
            Http::post('http://localhost:3000/session/logout', ['session_id' => $gateway->session_id]);
        } catch (\Exception $e) {}

        $gateway->delete();
        return back()->with('success', 'Gateway dihapus.');
    }

    // Halaman Scan QR per Gateway
    public function scan($id)
    {
        $gateway = WhatsappGateway::findOrFail($id);
        return view('admin.whatsapp.scan', compact('gateway'));
    }
}