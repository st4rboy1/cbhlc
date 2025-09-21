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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('enrollment_id')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // parent who enrolled
            $table->string('school_year');
            $table->string('quarter')->nullable(); // Will use Quarter enum in model
            $table->enum('status', ['pending', 'approved', 'rejected', 'enrolled'])->default('pending');

            // Billing Information (stored in cents for precision)
            $table->integer('tuition_fee_cents');
            $table->integer('miscellaneous_fee_cents')->default(0);
            $table->integer('laboratory_fee_cents')->default(0);
            $table->integer('library_fee_cents')->default(0);
            $table->integer('sports_fee_cents')->default(0);
            $table->integer('total_amount_cents');
            $table->integer('discount_cents')->default(0);
            $table->integer('net_amount_cents');

            // Payment Status (stored in cents for precision)
            $table->string('payment_status')->default('pending'); // Will use PaymentStatus enum in model
            $table->integer('amount_paid_cents')->default(0);
            $table->integer('balance_cents');
            $table->date('payment_due_date')->nullable();

            // Additional Information
            $table->text('remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
