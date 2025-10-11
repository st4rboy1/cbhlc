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
        Schema::create('enrollment_periods', function (Blueprint $table) {
            $table->id();
            $table->string('school_year', 9)->unique(); // e.g., "2025-2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->date('early_registration_deadline')->nullable();
            $table->date('regular_registration_deadline');
            $table->date('late_registration_deadline')->nullable();
            $table->string('status')->default('upcoming'); // upcoming, active, closed
            $table->text('description')->nullable();
            $table->boolean('allow_new_students')->default(true);
            $table->boolean('allow_returning_students')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('school_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_periods');
    }
};
