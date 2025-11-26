<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    // List Mata Pelajaran
    public function index()
    {
        $subjects = Subject::orderBy('name')->get();
        return view('subjects.index', compact('subjects'));
    }

    // Form Tambah
    public function create()
    {
        return view('subjects.create');
    }

    // Simpan
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name|max:255'
        ]);

        Subject::create(['name' => $request->name]);

        return redirect()->route('subjects.index')->with('success', 'Mata Pelajaran Berhasil Ditambahkan!');
    }

    // Hapus
    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();
        return redirect()->route('subjects.index')->with('success', 'Data Dihapus!');
    }
}
