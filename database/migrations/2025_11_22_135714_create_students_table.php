<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nis')->unique(); // Ini yang akan jadi isi QR Code
            $table->string('name');
            // Ganti $table->string('class_name'); Menjadi:
            // $table->foreignUuid('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignUuid('classroom_id')->nullable()->constrained('classrooms')->onDelete('cascade');
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
