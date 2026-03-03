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
         Schema::table('majors', function (Blueprint $table) {
            $table->string('program_name')->nullable()->after('name');
            $table->string('head_of_major')->nullable()->after('program_name');
            $table->string('head_of_workshop')->nullable()->after('head_of_major');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            $table->dropColumn(['program_name', 'head_of_major', 'head_of_workshop']);
        });
    }
};
