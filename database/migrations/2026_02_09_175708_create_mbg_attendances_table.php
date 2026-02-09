<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbg_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date'); // Untuk membatasi 1x per hari
            $table->dateTime('check_in_time');
            $table->string('status')->default('taken'); // 'taken' = sudah ambil
            $table->string('method'); // 'barcode', 'face', atau 'manual'
            $table->string('image_evidence')->nullable(); // Path foto bukti (wajah saat ambil)
            $table->string('recorded_by')->nullable(); // Siapa petugas yg jaga (opsional)
            $table->timestamps();

            // Index agar pencarian cepat saat scan ribuan siswa
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbg_attendances');
    }
};
