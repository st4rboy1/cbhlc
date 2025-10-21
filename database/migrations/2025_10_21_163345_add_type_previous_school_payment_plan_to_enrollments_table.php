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
            $table->string('type')->default('new')->after('grade_level'); // new, continuing, returnee, transferee
            $table->string('previous_school')->nullable()->after('type');
            $table->string('payment_plan')->default('monthly')->after('previous_school'); // annual, semestral, quarterly, monthly
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['type', 'previous_school', 'payment_plan']);
        });
    }
};
