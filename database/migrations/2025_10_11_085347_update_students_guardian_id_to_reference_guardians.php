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
        Schema::table('students', function (Blueprint $table) {
            // Drop existing foreign key constraint to users table
            $table->dropForeign(['guardian_id']);

            // Add new foreign key constraint to guardians table
            $table->foreign('guardian_id')
                ->references('id')
                ->on('guardians')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop foreign key constraint to guardians table
            $table->dropForeign(['guardian_id']);

            // Restore original foreign key constraint to users table
            $table->foreign('guardian_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
