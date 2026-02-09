<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MbgAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mbg_attendances';

    protected $fillable = [
        'student_id',
        'date',
        'check_in_time',
        'status',
        'method',
        'image_evidence',
        'recorded_by',
    ];

    // Relasi ke Siswa
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
