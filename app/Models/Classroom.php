<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // Pastikan Trait ini ada

class Classroom extends Model
{
    use HasUuid;

    protected $fillable = ['name', 'major_id'];

    // Relasi: Satu Kelas punya banyak Siswa
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Relasi: Satu Kelas punya banyak Jadwal Pelajaran
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }



}
