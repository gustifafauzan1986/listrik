<?php

namespace App\Http\Controllers;

use App\Models\InventoryLoan;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryLoanController extends Controller
{
    // Simpan Peminjaman Baru
    public function store(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'borrower_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'loan_date' => 'required|date',
        ]);

        $item = Inventory::findOrFail($request->inventory_id);

        // Validasi stok (Opsional: Cek apakah stok cukup)
        if ($item->quantity < $request->quantity) {
            return redirect()->back()->withErrors(['quantity' => 'Stok barang tidak mencukupi untuk dipinjam.']);
        }

        InventoryLoan::create([
            'inventory_id' => $request->inventory_id,
            'borrower_name' => $request->borrower_name,
            'quantity' => $request->quantity,
            'loan_date' => $request->loan_date,
            'status' => 'dipinjam',
            'notes' => $request->notes
        ]);

        // Opsional: Kurangi stok utama jika sistemnya mengurangi stok saat dipinjam
        // $item->decrement('quantity', $request->quantity);

        return redirect()->back()->with('success', 'Peminjaman berhasil dicatat.');
    }

    // Proses Pengembalian Barang
    // public function returnItem(Request $request, $id)
    // {
    //     $loan = InventoryLoan::findOrFail($id);

    //     $loan->update([
    //         'status' => 'kembali',
    //         'return_date' => Carbon::now(),
    //         'notes' => $request->notes // Catatan kondisi saat kembali
    //     ]);

    //     // Opsional: Tambah stok lagi
    //     // $loan->inventory->increment('quantity', $loan->quantity);

    //     return redirect()->back()->with('success', 'Barang berhasil dikembalikan.');
    // }

    // Proses Pengembalian Barang
    public function returnItem(Request $request, $id)
    {
        $loan = InventoryLoan::findOrFail($id);

        $loan->update([
            'status' => 'kembali',
            'return_date' => Carbon::now(),
            'notes' => $request->notes // Catatan kondisi saat kembali
        ]);

        // Jika request dari AJAX (Modal), kembalikan JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Barang berhasil dikembalikan.']);
        }

        return redirect()->back()->with('success', 'Barang berhasil dikembalikan.');
    }

    // [METODE BARU] API: Ambil Daftar Peminjaman Aktif untuk Modal
    public function activeLoans()
    {
        $loans = InventoryLoan::with('inventory') // Load relasi barang
                    ->where('status', 'dipinjam')
                    ->orderBy('loan_date', 'desc')
                    ->get();

        return response()->json($loans);
    }
}
