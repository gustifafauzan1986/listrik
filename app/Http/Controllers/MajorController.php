<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Major;

class MajorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $majors = Major::orderBy('name')->get();
        return view('majors.index', compact('majors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('majors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // VALIDASI TAMBAH DATA
        $request->validate([
            // Nama tabel diasumsikan 'majors'. Jika tabel Anda 'subjects', ganti 'majors' jadi 'subjects'
            'name' => 'required|string|max:255|unique:majors,name', 
            'code' => 'required|string|max:20|unique:majors,code',
        ], [
            // Custom Error Messages (Bahasa Indonesia)
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique'   => 'Nama jurusan ini sudah terdaftar di database.',
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique'   => 'Kode jurusan ini sudah digunakan.',
        ]);

        Major::create([
            'name' => strtoupper($request->name),
            'code' => strtoupper($request->code)
        ]);

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $major = Major::findOrFail($id);
        return view('majors.edit', compact('major'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // VALIDASI UPDATE DATA
        $request->validate([
            // unique:table,column,except_id
            'name' => 'required|string|max:255|unique:majors,name,' . $id,
            'code' => 'required|string|max:20|unique:majors,code,' . $id,
        ], [
            // Custom Error Messages
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique'   => 'Nama jurusan ini sudah terdaftar (tidak boleh kembar).',
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique'   => 'Kode jurusan ini sudah digunakan oleh jurusan lain.',
        ]);

        $major = Major::findOrFail($id);
        
        $major->update([
            'name' => strtoupper($request->name),
            'code' => strtoupper($request->code)
        ]);

        return redirect()->route('majors.index')
                ->with('success', 'Data Jurusan berhasil diperbarui!'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $major = Major::findOrFail($id);
        
        // Opsional: Validasi Relasi (misal: jangan hapus jika ada siswa di jurusan ini)
        // if($major->students()->exists()) {
        //    return back()->with('error', 'Gagal hapus! Masih ada siswa di jurusan ini.');
        // }

        $major->delete();

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil dihapus!');
    }
}