<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Attendance extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    protected $guarded = [];

    // --- BAGIAN INI YANG HILANG/ERROR ---
    
    // Relasi ke Siswa (belongsTo = Milik satu siswa)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi ke Jadwal (belongsTo = Milik satu jadwal)
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
