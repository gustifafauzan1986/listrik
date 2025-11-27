<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Mengubah kolom ENUM untuk mendukung status baru
        // Perintah ini spesifik untuk MySQL/MariaDB
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpa') NOT NULL DEFAULT 'alpa'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('hadir', 'terlambat') NOT NULL");
    }
};
