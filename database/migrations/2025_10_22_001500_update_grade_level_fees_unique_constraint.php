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
        Schema::table('grade_level_fees', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['grade_level', 'school_year']);

            // Add new unique constraint including payment_terms
            $table->unique(['grade_level', 'school_year', 'payment_terms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_level_fees', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['grade_level', 'school_year', 'payment_terms']);

            // Restore the old unique constraint
            $table->unique(['grade_level', 'school_year']);
        });
    }
};
