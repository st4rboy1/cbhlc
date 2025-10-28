<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run for MySQL - SQLite doesn't support ENUM
        // SQLite will store as TEXT and validation happens at application layer
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY COLUMN gender ENUM('Male', 'Female', 'Other') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY COLUMN gender ENUM('Male', 'Female') NOT NULL");
        }
    }
};
