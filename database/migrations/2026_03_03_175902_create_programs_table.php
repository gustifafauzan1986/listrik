<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Nama Program Keahlian
            $table->string('code')->unique(); // Kode Program (misal: TE)
            // $table->string('head_of_program')->nullable(); // Ketua Program Keahlian
            $table->foreignUuid('program_teacher_id')->constrained('teachers')->nullable(); // Penilai (Guru Pembimbing)
            $table->timestamps();
        });

        // Menambahkan foreign key ke tabel majors agar terhubung ke programs
        Schema::table('majors', function (Blueprint $table) {
            $table->foreignUuid('program_id')->nullable()->after('id');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');
            $table->foreignUuid('workshop_teacher_id')->constrained('teachers')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
        Schema::dropIfExists('programs');
    }
};
