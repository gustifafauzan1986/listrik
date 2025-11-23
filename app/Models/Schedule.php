<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Schedule extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    protected $guarded = [];
}
