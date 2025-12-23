<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DailyReportController;
use App\Models\Student;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

use App\Http\Controllers\UserImportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SettingController;

use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\UpdateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleImportController;
use App\Http\Controllers\TeacherImportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\StudentAreaController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ScannerDeviceController;

use App\Http\Controllers\StudentPermissionController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PiketDashboardController;
use App\Http\Controllers\SiswaDashboardController;
use App\Services\GithubVersionChecker; // Service Pengecekan Versi

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


// Route untuk Halaman Blokir jika aplikasi kedaluwarsa (Fitur GitHub Check)
Route::get('/update-required', function () {
    $checker = new GithubVersionChecker();
    if (!$checker->isOutdated()) {
        return redirect('/');
    }
    return view('errors.update_required');
})->name('app.update_required');

require __DIR__.'/auth.php';

Route::get('/logout', [UserController::class, 'Logout'])->name('user.logout');
Route::get('/profile', [UserController::class, 'Profile'])->name('user.profile');
Route::post('/profile/store', [UserController::class, 'profileStore'])->name('profile.store');
Route::get('/change/password', [UserController::class, 'password'])->name('user.password');
Route::post('/update/password', [UserController::class, 'updatePassword'])->name('update.password');

// =========================================================================
// GROUP : MONITORING REALTIME (Semua User Login)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    // Halaman Monitor Dashboard (AJAX Polling)
    //Route::get('/daily-attendance/monitor', [DailyAttendanceController::class, 'monitor'])->name('daily.monitor');
    Route::get('/daily-attendance/monitor-kelas', [DailyAttendanceController::class, 'monitorKelas'])->name('daily.monitor.kelas');
    // API Data JSON untuk Monitor
    Route::get('/daily-attendance/api/latest', [DailyAttendanceController::class, 'getRealtimeData'])->name('daily.api.latest');

    // --- DASHBOARD REDIRECTOR (UPDATED) ---
    // Logika pengalihan user ke halaman yang sesuai role-nya
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});


Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:guru'])->group(function () {
        // Halaman Scanner (Hanya bisa diakses Guru yang login)
        Route::get('/scan/{schedule_id}', [AttendanceController::class, 'index'])->name('scan.index');
        // Proses Data Scan (Ajax)
        Route::post('/scan/store', [AttendanceController::class, 'store'])->name('scan.store');
        // Scanner Wajah (API & View)
        Route::get('/face/descriptors/{schedule_id}', [FaceController::class, 'getDescriptors']);
        Route::get('/scan-face/{schedule_id}', [FaceController::class, 'scan'])->name('scan.face');
        // Route::resource('schedule', ScheduleController::class);
        Route::prefix('guru/schedule')->name('schedule.')->group(function() {
            Route::get('/', [ScheduleController::class, 'index'])->name('index'); 
            Route::get('/create', [ScheduleController::class, 'create'])->name('create');
            Route::post('/store', [ScheduleController::class, 'store'])->name('store');
            Route::get('/show/{id}', [ScheduleController::class, 'show'])->name('show');
            Route::get('/edit/{id}', [ScheduleController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [ScheduleController::class, 'update'])->name('update');
            Route::delete('/destroy/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
        });

         // --- 3. ABSENSI MANUAL (BARU) ---
        Route::get('/schedule/manual/{id}', [AttendanceController::class, 'createManual'])->name('attendance.manual');
        Route::post('/schedule/manual/{id}', [AttendanceController::class, 'storeManual'])->name('attendance.storeManual');
        Route::get('/print-cards-siswa', [StudentAreaController::class, 'index'])->name('print.siswa.index');
        // Cetak Full 1 Kelas
        Route::get('/print-cards-siswa/class/{id}', [StudentAreaController::class, 'printByClass'])->name('print.siswa.class');

        // [BARU] Pilih Siswa Tertentu & Cetak
        Route::get('/print-cards-siswa/select/{id}', [StudentAreaController::class, 'selectStudents'])->name('print.siswa.select');
        Route::post('/print-cards-siswa/print-selected', [StudentAreaController::class, 'printSelected'])->name('print.siswa.selected');

        // Cetak Semua & Satuan
        Route::get('/print-cards-siswa/all', [StudentAreaController::class, 'printAll'])->name('print.siswa.all');
        Route::get('/print-card-siswa/{id}', [StudentAreaController::class, 'printSingle'])->name('print.siswa.single');

        // Route Khusus Dashboard Guru
        Route::get('/guru/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');

    });
    // =========================================================================
    // GROUP 1: AREA GURU & ADMIN (Operasional Harian)
    // =========================================================================

    Route::middleware(['role:admin|guru|piket'])->group(function () {

        Route::get('/piket/dashboard', [PiketDashboardController::class, 'dashboard'])->name('piket.dashboard');
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::post('/report/print', [ReportController::class, 'print'])->name('report.print');
        // [BARU] Route Cetak Laporan Per Jadwal (Direct Link)
        Route::get('/report/schedule/{id}', [ReportController::class, 'printSchedule'])->name('report.schedule');
        Route::resource('subjects', SubjectController::class);

        // Route untuk mencetak Laporan Riwayat Siswa (Transkrip)
        // Memanggil method ReportController@printStudent
        Route::get('/report/student/{id}', [ReportController::class, 'printStudent'])->name('report.student');


        Route::get('/daily-attendance', [DailyAttendanceController::class, 'index'])->name('daily.index');
        Route::post('/daily-attendance', [DailyAttendanceController::class, 'store'])->name('daily.store');

        // 1. Route Menampilkan Form Manual (GET)
        // Sesuai dengan method create() di controller
        Route::get('/daily-attendance/manual', [DailyAttendanceController::class, 'create'])->name('daily.create');

        Route::get('/absensi/report', [DailyAttendanceController::class, 'report'])->name('daily_attendance.report');
        //Route::get('/absensi/laporan', [DailyAttendanceController::class, 'laporan'])->name('daily_attendance.report');
        Route::get('/report/absensi/student/{id}', [DailyReportController::class, 'printStudentAbsensi'])->name('report.absensi.student');
        Route::get('/absensi/report', [DailyReportController::class, 'reportDaily'])->name('daily_attendance.report');
        Route::post('/report/absensi/print', [DailyReportController::class, 'printAbsensi'])->name('report.print.absensi');

        // 2. Route Menyimpan Data Manual (POST)
        // Sesuai dengan method storeManual() di controller
        Route::post('/daily-attendance/manual', [DailyAttendanceController::class, 'storeManual'])->name('daily.storeManual');

        Route::get('/api/realtime-stats', [DashboardController::class, 'getRealtimeStats'])->name('api.stats');

        // --- B. ABSENSI HARIAN (GERBANG WAJAH) [BARU] ---
    // Scan Masuk & Pulang via Wajah
        Route::get('/daily-face-scan', [FaceController::class, 'dailyScan'])->name('daily.face.scan');
        Route::get('/face/all-descriptors', [FaceController::class, 'getAllDescriptors'])->name('face.descriptors.all');
    });

    // =========================================================================
    // GROUP 2: KHUSUS ADMIN (Master Data & Settings)
    // =========================================================================
    Route::middleware(['role:admin'])->group(function () {

        // ... route import siswa yang lama ...

        // ROUTE BARU: IMPORT USER
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
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
        //Route::get('/print', [PrintController::class, 'index'])->name('print.index');
        // Route::get('/print-all-cards', function () {
        //     $students = Student::all();
        //     return view('print.all_cards', compact('students'));
        // });

        Route::get('/face/register', [FaceController::class, 'index'])->name('face.index');
        Route::get('/face/register/{id}', [FaceController::class, 'register'])->name('face.register');
        Route::post('/face/register/{id}', [FaceController::class, 'store'])->name('face.store');

        // --- 4. WHATSAPP GATEWAY (MANUAL BROADCAST) ---
        Route::get('/whatsapp/test', [WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::post('/whatsapp/send', [WhatsAppController::class, 'store'])->name('whatsapp.store');


        // --- 2. PENGATURAN SEKOLAH (SETTINGS) ---
        // Route ini diperlukan oleh form di settings/index.blade.php
        Route::prefix('admin/settings')->name('settings.')->group(function() {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/update', [SettingController::class, 'update'])->name('update');
            // Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            // Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        
        
            Route::get('/attendance', [SettingController::class, 'settingAttendance'])->name('attendance');
            Route::post('/attendance', [SettingController::class, 'updateAttendance'])->name('update.attendance');
        });
        Route::get('/user/all', [UserController::class, 'allUser'])->name('all.user');
        Route::get('/user/add', [UserController::class, 'addUser'])->name('add.user');
        Route::post('/user/store', [UserController::class, 'storeUser'])->name('store.user');
        Route::post('/update-user/status',[UserController::class, 'UpdateStatusUser'])->name('update.status.user');

        // Menampilkan halaman update
        Route::get('/system/update', [UpdateController::class, 'index'])->name('system.update.index');

        // Memproses update (POST)
        Route::post('/system/update', [UpdateController::class, 'doUpdate'])->name('system.update.run');

        // Import Skedule
        Route::get('/import-jadwal', [ScheduleImportController::class, 'index'])->name('jadwal.import');
        Route::post('/import-jadwal', [ScheduleImportController::class, 'store'])->name('jadwal.import.store');

        // Import Guru (Data Lengkap) - ROUTE BARU
        Route::get('/import-teachers', [TeacherImportController::class, 'index'])->name('teachers.import');
        Route::post('/import-teachers', [TeacherImportController::class, 'store'])->name('teachers.import.store');

        // [BARU] MANAJEMEN GURU (CRUD)
        // Ini menangani route teachers.index, teachers.edit, teachers.update, teachers.destroy
        // Route::resource('teachers', TeacherController::class);
        // Route::resource('teachers', TeacherController::class)->except(['create', 'store', 'show']);
        Route::resource('classrooms', ClassroomController::class);
        Route::resource('majors', MajorController::class);

        Route::patch('/students/{id}/remove-class', [StudentController::class, 'removeClassroom'])->name('students.remove_class');

         // Route khusus cetak ID Card (Fitur yang baru ditambahkan)
        Route::get('/students/{id}/print-id', [StudentController::class, 'printIdCard'])->name('students.print_id');
        // Route khusus cetak ID Card (PDF Download/Stream) - FITUR BARU
        Route::get('/students/{id}/print-id-pdf', [StudentController::class, 'printIdCardPdf'])->name('students.print_id_pdf');

        // Route untuk mengeluarkan siswa dari kelas (Unassign)
        Route::patch('/students/{id}/remove-class', [StudentController::class, 'removeClass'])->name('students.remove_class');
        // --- 6. WHATSAPP GATEWAY (BROADCAST) ---
        Route::get('/whatsapp/test', [WhatsAppController::class, 'index'])->name('whatsapp.index'); // Manual 1 nomor
        Route::post('/whatsapp/send', [WhatsAppController::class, 'store'])->name('whatsapp.store');

        Route::get('/whatsapp/broadcast', [WhatsAppController::class, 'broadcast'])->name('whatsapp.broadcast'); // Broadcast Kelas
        Route::post('/whatsapp/broadcast', [WhatsAppController::class, 'sendBroadcast'])->name('whatsapp.broadcast.send');

        // Manajemen Siswa
        Route::get('/students/export', [StudentController::class, 'export'])->name('students.export'); // [BARU]
         // Manajemen Guru
        Route::get('/teachers/export', [TeacherController::class, 'export'])->name('teachers.export'); // [BARU]
        // Manajemen Kelas
        Route::get('/classrooms/export', [ClassroomController::class, 'export'])->name('classrooms.export'); // [BARU]

          // Route Cetak ID Card Massal per Kelas (PDF)
        Route::get('/classrooms/{id}/print-ids', [ClassroomController::class, 'printAllIdsPdf'])->name('classrooms.print_ids');
        Route::get('/teachers/export', [TeacherController::class, 'export'])->name('teachers.export');
        // PERBAIKAN: method 'show' tidak lagi di-exclude agar halaman detail guru bisa diakses
        Route::resource('teachers', TeacherController::class)->except(['create', 'store']);

        // Route::resource('students', StudentController::class)->except(['create', 'show', 'store', 'index']);
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/update/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::get('/students/destroy/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

        // --- 4. MANAJEMEN KARTU SISWA ---
        Route::get('/print-cards', [CardController::class, 'index'])->name('print.index');

        // Cetak Full 1 Kelas
        Route::get('/print-cards/class/{id}', [CardController::class, 'printByClass'])->name('print.class');

        // [BARU] Pilih Siswa Tertentu & Cetak
        Route::get('/print-cards/select/{id}', [CardController::class, 'selectStudents'])->name('print.select');
        Route::post('/print-cards/print-selected', [CardController::class, 'printSelected'])->name('print.selected');

        // Cetak Semua & Satuan
        Route::get('/print-cards/all', [CardController::class, 'printAll'])->name('print.all');
        Route::get('/print-card/{id}', [CardController::class, 'printSingle'])->name('print.single');

        Route::get('/transkrip', [TranscriptController::class, 'index'])->name('reports.transcript.index');
        Route::get('/transkrip/cetak', [TranscriptController::class, 'show'])->name('reports.transcript.show');
        Route::get('/transkrip/cetak-kelas', [TranscriptController::class, 'printByClass'])->name('reports.transcript.class');

        // Route::resource('teaching-assignments', TeachingAssignmentController::class);
        Route::get('/teaching-assignments', [TeachingAssignmentController::class, 'index'])->name('teaching-assignments.index');
        Route::get('/teaching-assignments/create', [TeachingAssignmentController::class, 'create'])->name('teaching-assignments.create');
        Route::post('/teaching-assignments/store', [TeachingAssignmentController::class, 'store'])->name('teaching-assignments.store');
        Route::delete('/teaching-assignments/destroy/{id}', [TeachingAssignmentController::class, 'destroy'])->name('teaching-assignments.destroy');

        Route::get('/reward/sertifikat', [CertificateController::class, 'index'])->name('certificates.index');
        Route::post('/reward/sertifikat/cetak', [CertificateController::class, 'generate'])->name('certificates.generate');
       // Route untuk Manajemen Perangkat Scanner & CCTV
        // Kita exclude 'create', 'store', 'show' karena registrasi dilakukan dari Frontend (Kiosk)
        // dan detail ditampilkan langsung di index/modal.
        Route::resource('scanner-devices', ScannerDeviceController::class)
            ->except(['create', 'show']);

        Route::get('/scan-camera', [ScannerDeviceController::class, 'scan'])->name('scan.camera');

        Route::post('/izin/store', [StudentPermissionController::class, 'store'])->name('izin.store');
        Route::post('/izin/show', [StudentPermissionController::class, 'show'])->name('izin.show');
        Route::post('/izin/check', [StudentPermissionController::class, 'check'])->name('izin.check');
        Route::post('/izin/return', [StudentPermissionController::class, 'markReturn'])->name('izin.return');
        Route::get('/izin/print/{id}', [StudentPermissionController::class, 'print'])->name('izin.print');


        // --- REKAPITULASI MENYELURUH (MASTER RECAP) ---
        // Pastikan RecapController sudah dibuat
        Route::prefix('rekapitulasi')->name('recap.')->group(function() {
            Route::get('/', [RecapController::class, 'index'])->name('index'); 
            Route::get('/daily', [RecapController::class, 'dailyLog'])->name('daily');
            Route::get('/learning', [RecapController::class, 'learningLog'])->name('learning');
            Route::get('/students', [RecapController::class, 'studentsList'])->name('students');
            Route::get('/students/{id}', [RecapController::class, 'studentDetail'])->name('student.detail');
        });

        // Menu Jadwal Semua Guru (Master Schedule)
        Route::get('/schedule/all', [ScheduleController::class, 'allSchedules'])->name('schedule.all');
        Route::post('/schedule/admin/store', [ScheduleController::class, 'storeAsAdmin'])->name('schedule.store_admin');

    });

    Route::middleware(['role:siswa'])->group(function () {

        // Profil Siswa (Edit No HP & Alamat)
        Route::get('/siswa/dashboard', [SiswaDashboardController::class, 'dashboard'])->name('students.dashboard');
        Route::get('/my-profile', [StudentAreaController::class, 'profileStudent'])->name('student.profile');
        Route::put('/my-profile', [StudentAreaController::class, 'updateProfile'])->name('student.profile.update');
        // Riwayat Absensi
        Route::get('/my-history/subject', [StudentAreaController::class, 'historySubject'])->name('student.history.subject');
        Route::get('/my-history/daily', [StudentAreaController::class, 'historyDaily'])->name('student.history.daily');

         // [BARU] Cetak Kartu Sendiri
        // Cetak Kartu Sendiri
        Route::get('/my-card', [StudentAreaController::class, 'printCard'])->name('student.print.card');

    });
});
