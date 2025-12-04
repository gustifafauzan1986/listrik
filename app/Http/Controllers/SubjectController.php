<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Tampilkan Daftar Mapel
     */
    public function index()
    {
        // Ambil data urut abjad
        $subjects = Subject::orderBy('name')->get();
        return view('subjects.index', compact('subjects'));
    }

    /**
     * Tampilkan Form Tambah Mapel
     */
    public function create()
    {
        return view('subjects.create');
    }

    /**
     * Proses Simpan Mapel Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name', // Nama harus unik
        ]);

        Subject::create([
            'name' => $request->name
        ]);

        return redirect()->route('subjects.index')->with('success', 'Mata Pelajaran berhasil ditambahkan!');
    }

    /**
     * Tampilkan Form Edit
     */
    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Proses Update Mapel
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $id,
        ]);

        $subject = Subject::findOrFail($id);
        $subject->update(['name' => $request->name]);

        return redirect()->route('subjects.index')->with('success', 'Mata Pelajaran berhasil diperbarui!');
    }

    /**
     * Hapus Mapel
     */
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        
        // Opsional: Cek apakah mapel sedang dipakai di jadwal?
        // if($subject->schedules()->exists()) {
        //    return back()->with('error', 'Gagal hapus! Mapel ini sedang digunakan di jadwal.');
        // }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Mata Pelajaran dihapus!');
    }
}