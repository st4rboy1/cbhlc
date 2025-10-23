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
            // Add school_year_id column (nullable initially for data migration)
            $table->foreignId('school_year_id')->nullable()->after('grade_level')->constrained('school_years')->onDelete('cascade');
        });

        // Migrate existing data: match school_year string to school_years.name
        $gradeLevelFees = DB::table('grade_level_fees')->whereNotNull('school_year')->get();

        foreach ($gradeLevelFees as $fee) {
            $schoolYear = DB::table('school_years')->where('name', $fee->school_year)->first();

            if ($schoolYear) {
                DB::table('grade_level_fees')
                    ->where('id', $fee->id)
                    ->update(['school_year_id' => $schoolYear->id]);
            }
        }

        // Make school_year_id required after migration
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
