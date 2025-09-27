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
            // Add invoice relationship
            $table->foreignId('invoice_id')->nullable()->after('net_amount_cents')
                ->constrained('invoices')->nullOnDelete();

            // Add payment reference for tracking
            $table->string('payment_reference', 100)->nullable()->after('invoice_id');

            // Add timestamps for payment workflow
            $table->timestamp('ready_for_payment_at')->nullable()->after('rejected_at');
            $table->timestamp('paid_at')->nullable()->after('ready_for_payment_at');

            // Add indexes for performance
            $table->index('invoice_id');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['payment_reference']);

            // Drop foreign key constraint
            $table->dropForeign(['invoice_id']);

            // Drop columns
            $table->dropColumn([
                'invoice_id',
                'payment_reference',
                'ready_for_payment_at',
                'paid_at'
            ]);
        });
    }
};