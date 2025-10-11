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
        Schema::table('enrollments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['guardian_id']);

            // Add the new foreign key constraint referencing guardians table
            $table->foreign('guardian_id')
                ->references('id')
                ->on('guardians')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['guardian_id']);

            // Restore the original foreign key constraint referencing users table
            $table->foreign('guardian_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
