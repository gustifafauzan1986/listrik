<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// App/Http/Controllers/DashboardController.php
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{

    public function getRealtimeStats()
    {
    $today = now()->format('Y-m-d');

    $data = [
        // Hitung total kehadiran hari ini
        'present_count' => Attendance::where('date', $today)
                            ->where('status', 'hadir')
                            ->count(),
                            
        // Hitung total terlambat hari ini
        'late_count'    => Attendance::where('date', $today)
                            ->where('status', 'terlambat')
                            ->count(),
                            
        // Total siswa terdaftar
        'total_students'=> User::count(),

        'user' => User::where('status', 1)->count(),
        'student' => Student::count(),
        'attendance' => Attendance::count(),
    ];

    return response()->json($data);
    }
}
