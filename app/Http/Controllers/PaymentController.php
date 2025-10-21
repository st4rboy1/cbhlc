<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * Download official receipt for a payment
     */
    public function downloadReceipt(Payment $payment)
    {
        $user = auth()->user();

        // Authorization: Admin or guardian who owns the student
        if (! $user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Guardian must own the student
            $guardian = Guardian::where('user_id', $user->id)->first();
            if (! $guardian) {
                abort(404);
            }

            $enrollment = $payment->invoice; // invoice_id is enrollment_id
            $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
                ->where('student_id', $enrollment->student_id)
                ->exists();

            if (! $hasAccess) {
                abort(404);
            }
        }

        $payment->load(['invoice.student', 'processedBy']);

        // Generate receipt number if not exists
        if (! $payment->receipt_number) {
            $payment->receipt_number = $this->generateReceiptNumber($payment);
            $payment->save();
        }

        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'payment' => $payment,
        ])
            ->setPaper('a4', 'portrait');

        $filename = "receipt-{$payment->receipt_number}-{$payment->payment_date->format('Ymd')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber(Payment $payment): string
    {
        $year = $payment->payment_date->format('Y');
        $month = $payment->payment_date->format('m');

        // Format: OR-YYYYMM-####
        $lastReceipt = Payment::whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->whereNotNull('receipt_number')
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt && preg_match('/OR-\d{6}-(\d{4})/', $lastReceipt->receipt_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('OR-%s%s-%04d', $year, $month, $nextNumber);
    }
}
