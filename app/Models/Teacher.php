<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Teacher extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'nip',
        'gender',
        'phone',
        'place_of_birth',
        'date_of_birth',
        'address',
        'education_level'
    ];

    /**
     * Relasi ke User (Akun Login)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Jadwal Mengajar
     * (Opsional: Jika nanti jadwal ingin direlasikan ke Teacher, bukan User)
     */
    public function schedules()
    {
        // Asumsi saat ini schedule masih pakai teacher_id (user_id)
        // Jika nanti diubah, relasinya bisa lewat sini
        return $this->hasMany(Schedule::class, 'teacher_id', 'user_id');
    }
}