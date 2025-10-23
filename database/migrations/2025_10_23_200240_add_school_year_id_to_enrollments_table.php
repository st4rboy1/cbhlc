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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->after('guardian_id')->constrained('school_years')->onDelete('restrict');
            $table->index('school_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropIndex(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
