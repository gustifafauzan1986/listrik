<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;

class TeacherController extends Controller
{
    /**
     * Tampilkan Daftar Guru
     */
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        // Fitur Pencarian (Nama, Email, NIP)
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhere('nip', 'LIKE', "%{$search}%");
        }

        // Pagination 10 data per halaman
        $teachers = $query->paginate(10);

        return view('teachers.index', compact('teachers'));
    }

    /**
     * Form Edit Guru
     */
    public function edit($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update Data Guru
     */
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        $request->validate([
            'name'   => 'required|string|max:255',
            'nip'    => 'nullable|string|max:50|unique:teachers,nip,' . $id,
            'phone'  => 'nullable|numeric',
            'gender' => 'nullable|in:L,P',
        ]);

        // 1. Update Data Login (Tabel User)
        $teacher->user->update([
            'name' => $request->name
        ]);

        // 2. Update Data Profil (Tabel Teacher)
        $teacher->update([
            'nip'     => $request->nip,
            'phone'   => $request->phone,
            'gender'  => $request->gender,
            'address' => $request->address,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Hapus Guru (Beserta Akun Loginnya)
     */
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Hapus usernya, otomatis teacher terhapus karena cascade delete
        $teacher->user->delete(); 

        return redirect()->route('teachers.index')->with('success', 'Data guru dan akun login berhasil dihapus.');
    }
}