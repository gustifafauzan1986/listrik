<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScannerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- AUTHENTICATION ROUTES ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Group untuk Device Scanner
Route::prefix('device')->group(function () {
    Route::post('/register', [ScannerController::class, 'registerDevice']);
    Route::post('/scan', [ScannerController::class, 'deviceScan']);
});

 Route::get('/students', [AttendanceApiController::class, 'getStudents']);
        Route::get('/staff', [AttendanceApiController::class, 'getStaff']);
        Route::post('/attendance', [AttendanceApiController::class, 'storeAttendance']);

