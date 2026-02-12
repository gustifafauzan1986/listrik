<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Industry;

class IndustryController extends Controller
{
    /**
     * Tampilkan daftar Industri / DU-DI
     */
    public function index(Request $request)
    {
        $query = Industry::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sector', 'like', '%' . $request->search . '%');
        }

        $industries = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('admin.industries.index', compact('industries'));
    }

    /**
     * Simpan data Industri baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'sector'         => 'nullable|string|max:255',
            'address'        => 'required|string',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'quota'          => 'required|integer|min:0',
        ]);

        Industry::create($request->all());

        return redirect()->back()->with('success', 'Data Industri / Tempat PKL berhasil ditambahkan.');
    }

    /**
     * Update data Industri
     */
    public function update(Request $request, $id)
    {
        $industry = Industry::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'sector'         => 'nullable|string|max:255',
            'address'        => 'required|string',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'quota'          => 'required|integer|min:0',
        ]);

        $industry->update($request->all());

        return redirect()->back()->with('success', 'Data Industri / Tempat PKL berhasil diperbarui.');
    }

    /**
     * Hapus data Industri
     */
    public function destroy($id)
    {
        $industry = Industry::findOrFail($id);
        
        // Opsional: Cek jika ada siswa yang masih PKL di sini sebelum dihapus
        if ($industry->internships()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal menghapus! Industri ini masih memiliki data penempatan siswa PKL.');
        }

        $industry->delete();

        return redirect()->back()->with('success', 'Data Industri / Tempat PKL berhasil dihapus.');
    }
}