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
            $table->boolean('info_requested')->default(false)->after('approved_by');
            $table->text('info_request_message')->nullable()->after('info_requested');
            $table->timestamp('info_request_date')->nullable()->after('info_request_message');
            $table->foreignId('info_requested_by')->nullable()->after('info_request_date')->constrained('users');
            $table->text('info_response_message')->nullable()->after('info_requested_by');
            $table->timestamp('info_response_date')->nullable()->after('info_response_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['info_requested_by']);
            $table->dropColumn([
                'info_requested',
                'info_request_message',
                'info_request_date',
                'info_requested_by',
                'info_response_message',
                'info_response_date',
            ]);
        });
    }
};
