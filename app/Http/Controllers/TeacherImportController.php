<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TeacherImport;

class TeacherImportController extends Controller
{
    public function index()
    {
        return view('teachers.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new TeacherImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Guru & Profil Berhasil Diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
}