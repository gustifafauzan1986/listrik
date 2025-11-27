<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Models\Student;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\ReportController;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

use App\Http\Controllers\UserImportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\CardController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:guru'])->group(function () {
        // Halaman Scanner (Hanya bisa diakses Guru yang login)
        Route::get('/scan/{schedule_id}', [AttendanceController::class, 'index'])->name('scan.index');
        // Proses Data Scan (Ajax)
        Route::post('/scan/store', [AttendanceController::class, 'store'])->name('scan.store');
        Route::resource('schedule', ScheduleController::class);
    });
    Route::middleware(['role:admin|guru'])->group(function () {

        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::post('/report/print', [ReportController::class, 'print'])->name('report.print');
        // [BARU] Route Cetak Laporan Per Jadwal (Direct Link)
        Route::get('/report/schedule/{id}', [ReportController::class, 'printSchedule'])->name('report.schedule');
        Route::resource('subjects', SubjectController::class);

        // Scanner Wajah (API & View)
        Route::get('/face/descriptors/{schedule_id}', [FaceController::class, 'getDescriptors']);
        Route::get('/scan-face/{schedule_id}', [FaceController::class, 'scan'])->name('scan.face');
    });

    Route::middleware(['role:admin'])->group(function () {

        // ... route import siswa yang lama ...

        // ROUTE BARU: IMPORT USER
        Route::get('/import-users', [UserImportController::class, 'index'])->name('users.import');
        Route::post('/import-users', [UserImportController::class, 'store'])->name('users.import.store');


        // ROUTE MANAGE ROLE (Resourceful Route)
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);

        // Melihat daftar siswa yang sudah absen di jadwal tertentu
        //Route::get('/my-schedule/{id}/attendances', [ScheduleController::class, 'show'])->name('schedule.show');
        // Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        // Route::get('/schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
        // Route::post('/schedule/store', [ScheduleController::class, 'store'])->name('schedule.store');
        // Route::post('/schedule/', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

        Route::get('/student-qr/{id}', function ($id) {
                $student = Student::findOrFail($id);
                // QR Code berisi NIS siswa
                return QrCode::size(300)->generate($student->nis);
            });
        // Import Siswa
            Route::get('/import-students', [StudentImportController::class, 'index'])->name('students.import');
            Route::post('/import-students', [StudentImportController::class, 'store'])->name('students.import.store');

        // Route untuk melihat satu kartu siswa (untuk testing)
        Route::get('/print-card/{id}', function ($id) {
            $student = Student::findOrFail($id);
            // Generate QR Code NIS
            $qrcode = QrCode::size(120)->generate($student->nis);
            return view('print.single_card', compact('student', 'qrcode'));
        });

        // Route untuk mencetak SEMUA kartu siswa sekaligus (mass print)
        Route::get('/print-all-cards', function () {
            $students = Student::all(); // Atau paginate jika siswanya ribuan
            return view('print.all_cards', compact('students'));
        });

        Route::get('/face/register', [FaceController::class, 'index'])->name('face.index');
        Route::get('/face/register/{id}', [FaceController::class, 'register'])->name('face.register');
        Route::post('/face/register/{id}', [FaceController::class, 'store'])->name('face.store');

        // --- 4. WHATSAPP GATEWAY (MANUAL BROADCAST) ---
    Route::get('/whatsapp/test', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/send', [WhatsAppController::class, 'store'])->name('whatsapp.store');
    });

});
