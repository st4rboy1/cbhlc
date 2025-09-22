<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Update payment status for an enrollment
     */
    public function updatePayment(Request $request, $enrollmentId)
    {
        $user = $request->user();

        // Only admin users can update payment status
        if (! $user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_status' => ['required', 'in:'.implode(',', PaymentStatus::values())],
            'remarks' => 'nullable|string|max:500',
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        $enrollment->amount_paid_cents = $validated['amount_paid'];
        $enrollment->payment_status = PaymentStatus::from($validated['payment_status']);
        $enrollment->balance_cents = $enrollment->net_amount_cents - $validated['amount_paid'];

        if (isset($validated['remarks']) && $validated['remarks']) {
            $enrollment->remarks = $validated['remarks'];
        }

        $enrollment->save();

        return redirect()->back()->with('success', 'Payment status updated successfully.');
    }
}
