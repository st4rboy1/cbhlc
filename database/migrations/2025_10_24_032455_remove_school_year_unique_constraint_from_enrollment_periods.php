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
        Schema::table('enrollment_periods', function (Blueprint $table) {
            // Drop the old unique constraint on school_year string
            $table->dropUnique('enrollment_periods_school_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $table) {
            // Restore the unique constraint on school_year string
            $table->unique('school_year');
        });
    }
};
