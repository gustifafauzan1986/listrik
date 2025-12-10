<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentImport;
// --- TAMBAHKAN BARIS INI ---
use Illuminate\Support\Facades\Artisan;

class StudentImportController extends Controller
{
    public function index()
    {
        return view('students.import');
    }

    public function store(Request $request)
    {
        // Validasi file harus Excel
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        // Proses Import
        Excel::import(new StudentImport, $request->file('file'));

        // --- TAMBAHAN: Panggil Command Generator ---
        Artisan::call('siswa:generate-users');

        return redirect()->back()->with('success', 'Data Siswa Berhasil Diimport!');
    }
}
