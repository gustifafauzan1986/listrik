<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryAdminController extends Controller
{
    /**
     * Menampilkan daftar barang dan stok saat ini
     */
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->has('search') && $request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $items = $query->latest()->paginate(10)->appends($request->all());
        // --- LOGIKA GENERATE KODE OTOMATIS ---
        $lastItem = Item::orderBy('code', 'desc')->first();
        if ($lastItem) {
            // Ambil angka dari kode terakhir (misal BRG-0005 diambil 5)
            $lastNumber = (int) substr($lastItem->code, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        // Format menjadi BRG-000X
        $autoCode = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('admin.inventory.index', compact('items', 'autoCode'));
    }

    /**
     * Menyimpan master barang baru
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:items,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'initial_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Buat master barang
            $item = Item::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'stock' => $request->initial_stock,
                'unit' => $request->unit,
                'year' => $request->year,
                'funding_source'    => $request->funding_source,
            ]);

            // 2. Jika stok awal > 0, catat sebagai barang masuk pertama
            if ($request->initial_stock > 0) {
                InventoryTransaction::create([
                    'item_id' => $item->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $request->initial_stock,
                    'date' => now(),
                    'notes' => 'Stok Awal',
                ]);
            }
        });

        return redirect()->route('admin.inventory.index')->with('success', 'Barang baru berhasil ditambahkan!');
    }

    /**
     * Mencatat transaksi Barang Masuk atau Keluar
     */
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|exists:items,id',
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'date'     => 'required|date',
            'notes'    => 'nullable|string',
        ]);

        $item = Item::findOrFail($request->item_id);

        // Validasi khusus untuk barang keluar (stok tidak boleh minus)
        if ($request->type === 'out' && $item->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi! Stok saat ini: ' . $item->stock]);
        }

        DB::transaction(function () use ($request, $item) {
            // 1. Catat riwayat transaksi
            InventoryTransaction::create([
                'item_id'  => $item->id,
                'user_id'  => Auth::id(),
                'type'     => $request->type,
                'quantity' => $request->quantity,
                'date'     => $request->date,
                'notes'    => $request->notes,
                'funding_source'    => $request->funding_source,
                'year'    => $request->year,
                'receiver'    => $request->receiver,
            ]);

            // 2. Update total stok di tabel master barang
            if ($request->type === 'in') {
                $item->increment('stock', $request->quantity);
            } else {
                $item->decrement('stock', $request->quantity);
            }
        });

        $tipe = $request->type === 'in' ? 'Masuk' : 'Keluar';
        return redirect()->route('admin.inventory.index')->with('success', "Transaksi Barang $tipe berhasil dicatat!");
    }

    /**
     * Menampilkan riwayat mutasi/transaksi barang
     */
    public function history(Request $request)
    {
        $transactions = InventoryTransaction::with(['item', 'user'])
                        ->latest('date')
                        ->latest('created_at')
                        ->paginate(15);

        return view('admin.inventory.history', compact('transactions'));
    }
}
