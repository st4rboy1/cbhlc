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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', [
                'birth_certificate',
                'report_card',
                'form_138',
                'good_moral',
                'other',
            ]);
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('mime_type');
            $table->timestamp('upload_date')->useCurrent();
            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('student_id');
            $table->index('document_type');
            $table->index('verification_status');
            $table->index(['student_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
