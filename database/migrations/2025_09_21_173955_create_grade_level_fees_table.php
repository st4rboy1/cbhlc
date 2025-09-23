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
        Schema::create('grade_level_fees', function (Blueprint $table) {
            $table->id();
            $table->string('grade_level');
            $table->integer('tuition_fee_cents'); // Store in cents for precision
            $table->integer('registration_fee_cents')->default(0);
            $table->integer('miscellaneous_fee_cents')->default(0);
            $table->integer('laboratory_fee_cents')->default(0);
            $table->integer('library_fee_cents')->default(0);
            $table->integer('sports_fee_cents')->default(0);
            $table->string('school_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: one fee structure per grade level per school year
            $table->unique(['grade_level', 'school_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_level_fees');
    }
};
