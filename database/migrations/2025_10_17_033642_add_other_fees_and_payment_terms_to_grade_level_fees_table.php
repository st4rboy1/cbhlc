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
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->integer('other_fees_cents')->default(0)->after('sports_fee_cents');
            $table->string('payment_terms')->default('ANNUAL')->after('other_fees_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropColumn(['other_fees_cents', 'payment_terms']);
        });
    }
};
