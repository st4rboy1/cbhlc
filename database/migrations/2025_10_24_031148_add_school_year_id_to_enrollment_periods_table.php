<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $table) {
            // Add school_year_id foreign key column
            $table->foreignId('school_year_id')
                ->nullable()
                ->after('id')
                ->constrained('school_years')
                ->onDelete('cascade');
        });

        // Populate school_year_id from existing school_year strings
        // Use different syntax for SQLite vs MySQL
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE enrollment_periods
                SET school_year_id = (
                    SELECT id FROM school_years
                    WHERE school_years.name = enrollment_periods.school_year
                    LIMIT 1
                )
            ');
        } else {
            DB::statement('
                UPDATE enrollment_periods ep
                SET school_year_id = (
                    SELECT id FROM school_years sy
                    WHERE sy.name = ep.school_year
                    LIMIT 1
                )
            ');
        }

        // Make school_year_id non-nullable after data migration
        Schema::table('enrollment_periods', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_periods', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
