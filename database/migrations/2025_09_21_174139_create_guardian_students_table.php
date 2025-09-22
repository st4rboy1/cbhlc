<?php

use App\Enums\RelationshipType;
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
        Schema::create('guardian_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('relationship_type', RelationshipType::values())->default(RelationshipType::GUARDIAN->value);
            $table->boolean('is_primary_contact')->default(false);
            $table->timestamps();

            // Ensure a guardian-student relationship is unique
            $table->unique(['guardian_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_students');
    }
};
