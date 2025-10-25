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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Recreate table without school_year_id
            $this->recreateTableForSQLite();
        } else {
            // MySQL: Use standard approach
            $this->migrateForMySQL();
        }
    }

    private function recreateTableForSQLite(): void
    {
        // Step 1: Create new table with correct schema
        Schema::create('grade_level_fees_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_period_id')->constrained('enrollment_periods')->cascadeOnDelete();
            $table->string('grade_level');
            $table->integer('tuition_fee_cents')->default(0);
            $table->integer('registration_fee_cents')->default(0);
            $table->integer('miscellaneous_fee_cents')->default(0);
            $table->integer('laboratory_fee_cents')->default(0);
            $table->integer('library_fee_cents')->default(0);
            $table->integer('sports_fee_cents')->default(0);
            $table->integer('other_fees_cents')->default(0);
            $table->integer('down_payment_cents')->default(0);
            $table->string('payment_terms');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['grade_level', 'enrollment_period_id', 'payment_terms'], 'grade_level_fees_unique');
        });

        // Step 2: Copy data with enrollment_period_id mapping
        DB::transaction(function () {
            $fees = DB::table('grade_level_fees')->get();

            foreach ($fees as $fee) {
                $period = DB::table('enrollment_periods')
                    ->where('school_year_id', $fee->school_year_id)
                    ->orderBy('start_date', 'asc')
                    ->first();

                if ($period) {
                    DB::table('grade_level_fees_new')->insert([
                        'id' => $fee->id,
                        'enrollment_period_id' => $period->id,
                        'grade_level' => $fee->grade_level,
                        'tuition_fee_cents' => $fee->tuition_fee_cents,
                        'registration_fee_cents' => $fee->registration_fee_cents,
                        'miscellaneous_fee_cents' => $fee->miscellaneous_fee_cents,
                        'laboratory_fee_cents' => $fee->laboratory_fee_cents,
                        'library_fee_cents' => $fee->library_fee_cents,
                        'sports_fee_cents' => $fee->sports_fee_cents,
                        'other_fees_cents' => $fee->other_fees_cents ?? 0,
                        'down_payment_cents' => $fee->down_payment_cents ?? 0,
                        'payment_terms' => $fee->payment_terms,
                        'is_active' => $fee->is_active,
                        'created_by' => $fee->created_by ?? null,
                        'created_at' => $fee->created_at,
                        'updated_at' => $fee->updated_at,
                    ]);
                }
            }
        });

        // Step 3: Drop old table and rename new one
        Schema::dropIfExists('grade_level_fees');
        Schema::rename('grade_level_fees_new', 'grade_level_fees');
    }

    private function migrateForMySQL(): void
    {
        // Step 1: Drop existing unique constraints
        $possibleIndexNames = [
            'grade_level_fees_grade_level_school_year_payment_terms_unique',
            'grade_level_fees_grade_level_school_year_id_payment_terms_unique',
        ];

        foreach ($possibleIndexNames as $indexName) {
            try {
                DB::statement("ALTER TABLE grade_level_fees DROP INDEX {$indexName}");
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        }

        // Step 2: Add enrollment_period_id column
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('enrollment_period_id')
                ->nullable()
                ->after('id')
                ->constrained('enrollment_periods')
                ->cascadeOnDelete();
        });

        // Step 3: Migrate data
        DB::transaction(function () {
            GradeLevelFee::chunk(100, function ($fees) {
                foreach ($fees as $fee) {
                    $period = EnrollmentPeriod::where('school_year_id', $fee->school_year_id)
                        ->orderBy('start_date', 'asc')
                        ->first();

                    if ($period) {
                        $fee->enrollment_period_id = $period->id;
                        $fee->save();
                    } else {
                        Log::warning("No enrollment period found for GradeLevelFee ID: {$fee->id}");
                    }
                }
            });
        });

        // Step 4: Make enrollment_period_id NOT NULL
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('enrollment_period_id')->nullable(false)->change();
        });

        // Step 5: Drop school_year_id column
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });

        // Step 6: Create new unique constraint
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->unique(['grade_level', 'enrollment_period_id', 'payment_terms'], 'grade_level_fees_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop the new unique constraint
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropUnique('grade_level_fees_unique');
        });

        // Step 2: Add school_year_id column back (nullable initially)
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('school_year_id')
                ->nullable()
                ->after('id')
                ->constrained('school_years')
                ->cascadeOnDelete();
        });

        // Step 3: Migrate data back from enrollment_period_id to school_year_id
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

        // Step 4: Make school_year_id NOT NULL after data migration
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable(false)->change();
        });

        // Step 5: Drop enrollment_period_id column and its foreign key
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropForeign(['enrollment_period_id']);
            $table->dropColumn('enrollment_period_id');
        });

        // Step 6: Restore the old unique constraint with school_year_id
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->unique(['grade_level', 'school_year_id', 'payment_terms']);
        });
    }
};
