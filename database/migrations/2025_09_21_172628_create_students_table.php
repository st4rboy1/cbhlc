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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->date('birthdate');
            $table->enum('gender', ['Male', 'Female']);
            $table->integer('age');
            $table->string('address');
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('guardian_name');
            $table->string('guardian_contact');
            $table->string('guardian_email')->nullable();
            $table->enum('grade_level', [
                'Nursery', 'Kinder 1', 'Kinder 2',
                'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
                'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12',
            ]);
            $table->string('section')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
