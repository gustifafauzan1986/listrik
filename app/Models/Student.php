<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Student extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Relasi ke Absensi
    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

}
