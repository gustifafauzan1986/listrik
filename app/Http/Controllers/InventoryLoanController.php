<?php

namespace App\Http\Controllers;

use App\Models\InventoryLoan;
use App\Models\Inventory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryLoanController extends Controller
{
    /**
     * Simpan Peminjaman Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'borrower_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'loan_date' => 'required|date',
        ]);

        $item = Inventory::findOrFail($request->inventory_id);

        // Validasi stok (Opsional, aktifkan jika perlu)
        // if ($item->quantity < $request->quantity) {
        //     return redirect()->back()->withErrors(['quantity' => 'Stok barang tidak mencukupi.']);
        // }

        InventoryLoan::create([
            'inventory_id' => $request->inventory_id,
            'borrower_name' => $request->borrower_name,
            'quantity' => $request->quantity,
            'loan_date' => $request->loan_date,
            'status' => 'dipinjam',
            'notes' => $request->notes
        ]);

        return redirect()->back()->with('success', 'Peminjaman berhasil dicatat.');
    }

    /**
     * Proses Pengembalian Barang
     */
    public function returnItem(Request $request, $id)
    {
        $loan = InventoryLoan::findOrFail($id);

        $loan->update([
            'status' => 'kembali',
            'return_date' => Carbon::now(),
            'notes' => $request->notes
        ]);

        // Support AJAX Response untuk Modal (agar tidak reload halaman)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Barang berhasil dikembalikan.']);
        }

        return redirect()->back()->with('success', 'Barang berhasil dikembalikan.');
    }

    /**
     * API: Ambil Daftar Riwayat Peminjaman (Aktif & Selesai)
     * Digunakan oleh Modal "Data Peminjaman" di halaman index via AJAX
     */
    public function activeLoans()
    {
        // Tampilkan 100 transaksi terakhir agar list tidak terlalu berat dan memuat riwayat
        $loans = InventoryLoan::with('inventory')
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();

        return response()->json($loans);
    }

    /**
     * Cetak Bukti Peminjaman / Pengembalian (PDF)
     */
    public function printProof($id)
    {
        // Ambil data peminjaman beserta detail barang dan ruangannya
        $loan = InventoryLoan::with(['inventory.room'])->findOrFail($id);

        // Ambil data sekolah untuk Kop Surat
        $school = $this->getSchoolData();

        $pdf = Pdf::loadView('pdf.inventory_loan_proof', compact('loan', 'school'));

        // Set ukuran kertas A5 Landscape agar hemat dan ringkas untuk bukti transaksi
        $pdf->setPaper('a5', 'landscape');

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'chroot' => public_path(),
        ]);

        // Nama file dinamis
        return $pdf->stream('Bukti_Peminjaman_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $loan->borrower_name) . '.pdf');
    }

    /**
     * Helper: Convert Image to Base64 (Untuk Kop Surat PDF agar anti-gagal loading gambar)
     */
    private function imageToBase64($path) {
        if (!$path || $path == '-') return null;
        $cleanPath = str_replace('storage/', '', $path);

        $pathsToCheck = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($path)
        ];

        foreach ($pathsToCheck as $fullPath) {
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return null;
    }

    /**
     * Helper: Get Data Sekolah dari Database Setting
     */
    private function getSchoolData()
    {
        return [
            'school_name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'school_address' => Setting::value('school_address', 'Alamat Sekolah'),
            'school_phone'   => Setting::value('school_phone', '-'),
            'school_web'     => Setting::value('school_web', '-'),
            'school_email'   => Setting::value('school_email', '-'),
            'logo_left'      => $this->imageToBase64(Setting::value('logo_left')),
            'logo_right'     => $this->imageToBase64(Setting::value('logo_right')),
            'sign_city'      => Setting::value('signature_city', 'Jakarta'),
            // Petugas Bengkel biasanya yang tanda tangan, bisa disesuaikan static string atau ambil dari setting
            'petugas_name'   => 'Petugas Bengkel'
        ];
    }
}
