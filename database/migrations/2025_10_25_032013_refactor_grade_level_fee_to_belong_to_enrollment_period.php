<?php

use App\Models\EnrollmentPeriod;
use App\Models\GradeLevelFee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add enrollment_period_id column (nullable initially for data migration)
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('enrollment_period_id')
                ->nullable()
                ->after('id')
                ->constrained('enrollment_periods')
                ->cascadeOnDelete();
        });

        // Step 2: Migrate existing data from school_year_id to enrollment_period_id
        DB::transaction(function () {
            GradeLevelFee::chunk(100, function ($fees) {
                foreach ($fees as $fee) {
                    // Find the first enrollment period for this school year
                    $period = EnrollmentPeriod::where('school_year_id', $fee->school_year_id)
                        ->orderBy('start_date', 'asc')
                        ->first();

                    if ($period) {
                        $fee->enrollment_period_id = $period->id;
                        $fee->save();
                    } else {
                        // Log warning for missing enrollment period
                        Log::warning("No enrollment period found for GradeLevelFee ID: {$fee->id}, SchoolYear ID: {$fee->school_year_id}");
                    }
                }
            });
        });

        // Step 3: Make enrollment_period_id NOT NULL after data migration
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('enrollment_period_id')->nullable(false)->change();
        });

        // Step 4: Drop school_year_id column and its foreign key
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add school_year_id column back (nullable initially)
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('school_year_id')
                ->nullable()
                ->after('id')
                ->constrained('school_years')
                ->cascadeOnDelete();
        });

        // Step 2: Migrate data back from enrollment_period_id to school_year_id
        DB::transaction(function () {
            GradeLevelFee::chunk(100, function ($fees) {
                foreach ($fees as $fee) {
                    // Get the school year through the enrollment period
                    $period = EnrollmentPeriod::find($fee->enrollment_period_id);

                    if ($period) {
                        $fee->school_year_id = $period->school_year_id;
                        $fee->save();
                    } else {
                        Log::warning("No enrollment period found for GradeLevelFee ID: {$fee->id} during rollback");
                    }
                }
            });
        });

        // Step 3: Make school_year_id NOT NULL after data migration
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable(false)->change();
        });

        // Step 4: Drop enrollment_period_id column and its foreign key
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropForeign(['enrollment_period_id']);
            $table->dropColumn('enrollment_period_id');
        });
    }
};
