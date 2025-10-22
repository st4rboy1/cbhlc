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
            $table->unsignedBigInteger('down_payment_cents')->default(0)->after('other_fees_cents')
                ->comment('Down payment required on enrollment (in cents) - for SEMESTRAL and MONTHLY payment terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_level_fees', function (Blueprint $table) {
            $table->dropColumn('down_payment_cents');
        });
    }
};
