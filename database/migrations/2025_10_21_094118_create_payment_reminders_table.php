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
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->enum('reminder_type', [
                'upcoming_7days',
                'upcoming_3days',
                'upcoming_1day',
                'overdue',
                'overdue_7days',
                'overdue_30days',
            ]);
            $table->timestamp('sent_at');
            $table->timestamp('email_opened_at')->nullable();
            $table->index(['enrollment_id', 'reminder_type']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
