<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Menambahkan kolom subject_id yang bisa bernilai NULL (nullable)
            // agar data lama tidak error, dan constrained ke tabel subjects
            $table->foreignId('subject_id')
                  ->nullable()
                  ->after('student_id')
                  ->constrained('subjects')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum kolomnya (Syntax array untuk dropForeign)
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
