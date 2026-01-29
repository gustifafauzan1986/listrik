<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Room;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Menampilkan daftar inventaris
     * Bisa difilter berdasarkan ruangan (bengkel)
     */
    public function index(Request $request)
    {
        $query = Inventory::with('room')->orderBy('name', 'asc');

        // Filter berdasarkan Ruangan/Bengkel
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter Kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $inventories = $query->paginate(20)->withQueryString();

        // Ambil semua ruangan untuk dropdown filter & modal
        $rooms = Room::orderBy('name')->get();

        return view('inventory.index', compact('inventories', 'rooms'));
    }

    /**
     * Simpan Data
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|unique:inventories,code',
            'category'=> 'required|in:alat,bahan,mesin',
            'quantity'=> 'required|integer|min:0',
            'condition'=> 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        Inventory::create($request->all());

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke inventaris.');
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|unique:inventories,code,' . $id,
            'category'=> 'required|in:alat,bahan,mesin',
            'quantity'=> 'required|integer|min:0',
            'condition'=> 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        $inventory->update($request->all());

        return redirect()->back()->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Hapus Data
     */
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();

        return redirect()->back()->with('success', 'Barang dihapus dari inventaris.');
    }
}
