<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Program extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'head_of_program',
    ];

    /**
     * Relasi ke Majors (Satu Program Keahlian memiliki banyak Konsentrasi/Major)
     */
    public function majors()
    {
        return $this->hasMany(Major::class);
    }
}
