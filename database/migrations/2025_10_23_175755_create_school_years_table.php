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
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "2024-2025"
            $table->integer('start_year'); // e.g., 2024
            $table->integer('end_year'); // e.g., 2025
            $table->date('start_date'); // Academic year start
            $table->date('end_date'); // Academic year end
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->boolean('is_active')->default(false); // Only one can be active
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('is_active');
            $table->index(['start_year', 'end_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_years');
    }
};
