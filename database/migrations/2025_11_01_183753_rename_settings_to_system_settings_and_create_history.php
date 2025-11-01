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
        // Rename settings table to system_settings
        Schema::rename('settings', 'system_settings');

        // Add additional fields to system_settings
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('type', 50)->default('string')->after('value'); // string, integer, boolean, json
            $table->text('description')->nullable()->after('type');
            $table->boolean('is_public')->default(false)->after('description'); // Whether setting is publicly accessible
        });

        // Create setting_history table for audit trail
        Schema::create('setting_history', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->index(['key', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop history table
        Schema::dropIfExists('setting_history');

        // Remove additional fields from system_settings
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'is_public']);
        });

        // Rename back to settings
        Schema::rename('system_settings', 'settings');
    }
};
