<?php

use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\AttendanceController;
use App\Models\Student;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/student-qr/{id}', function ($id) {
    $student = Student::findOrFail($id);
    // QR Code berisi NIS siswa
    return QrCode::size(300)->generate($student->nis);
});

// Halaman Scanner (Hanya bisa diakses Guru yang login)
Route::get('/scan', [AttendanceController::class, 'index'])->name('scan.index');
// Proses Data Scan (Ajax)
Route::post('/scan/store', [AttendanceController::class, 'store'])->name('scan.store');
