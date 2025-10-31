<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing data to lowercase
        DB::table('grade_level_fees')->update([
            'payment_terms' => DB::raw('LOWER(payment_terms)'),
        ]);

        Schema::table('grade_level_fees', function (Blueprint $table) {
            // Change the default value
            $table->string('payment_terms')->default('annual')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert existing data to uppercase
        DB::table('grade_level_fees')->update([
            'payment_terms' => DB::raw('UPPER(payment_terms)'),
        ]);

        Schema::table('grade_level_fees', function (Blueprint $table) {
            // Change the default value back to uppercase
            $table->string('payment_terms')->default('ANNUAL')->change();
        });
    }
};
