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
            $table->enum('semester', ['First', 'Second', 'Summer'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'enrolled'])->default('pending');

            // Billing Information
            $table->decimal('tuition_fee', 10, 2);
            $table->decimal('miscellaneous_fee', 10, 2)->default(0);
            $table->decimal('laboratory_fee', 10, 2)->default(0);
            $table->decimal('library_fee', 10, 2)->default(0);
            $table->decimal('sports_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);

            // Payment Status
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
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
