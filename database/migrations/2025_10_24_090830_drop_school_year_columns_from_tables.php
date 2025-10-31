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
        // Drop school_year column from enrollments table
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('school_year');
        });

        // Drop school_year column from enrollment_periods table
        Schema::table('enrollment_periods', function (Blueprint $table) {
            $table->dropIndex('enrollment_periods_school_year_index');
            $table->dropColumn('school_year');
        });

        // Drop school_year column from grade_level_fees table
        Schema::table('grade_level_fees', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique(['grade_level', 'school_year', 'payment_terms']);
            $table->dropColumn('school_year');
            // Add new unique constraint with school_year_id
            $table->unique(['grade_level', 'school_year_id', 'payment_terms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore school_year column to enrollments table
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('school_year')->nullable()->after('guardian_id');
        });

        // Restore school_year column to enrollment_periods table
        Schema::table('enrollment_periods', function (Blueprint $table) {
            $table->string('school_year', 9)->nullable()->after('id');
            $table->index('school_year');
        });

        // Restore school_year column to grade_level_fees table
        Schema::table('grade_level_fees', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique(['grade_level', 'school_year_id', 'payment_terms']);
            $table->string('school_year')->nullable()->after('grade_level');
            // Restore old unique constraint
            $table->unique(['grade_level', 'school_year', 'payment_terms']);
        });
    }
};
