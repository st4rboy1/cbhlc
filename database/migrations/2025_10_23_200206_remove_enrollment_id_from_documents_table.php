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
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->dropIndex(['enrollment_id']);
            $table->dropColumn('enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('enrollment_id')->nullable()->after('student_id')->constrained()->onDelete('cascade');
            $table->index('enrollment_id');
        });
    }
};
