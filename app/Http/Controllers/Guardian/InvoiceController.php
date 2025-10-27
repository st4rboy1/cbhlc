<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices for the guardian's children
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->firstOrFail();
        $studentIds = $guardian->children()->pluck('students.id');

        $enrollments = Enrollment::with(['student', 'guardian', 'schoolYear'])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->paginate(10);

        return Inertia::render('guardian/invoices/index', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Display the invoice for a specific enrollment
     */
    public function show(Request $request, Enrollment $invoice)
    {
        $user = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->firstOrFail();
        $studentIds = $guardian->children()->pluck('students.id');

        // Verify guardian owns this student
        if (! $studentIds->contains($invoice->student_id)) {
            abort(404);  // Return 404 for security
        }

        // Load related data
        $invoice->load(['student', 'guardian', 'schoolYear']);
        $settings = Setting::pluck('value', 'key');

        return Inertia::render('shared/invoice', [
            'enrollment' => $invoice,
            'invoiceNumber' => $invoice->enrollment_id ?? 'No Invoice Available',
            'currentDate' => now()->format('F d, Y'),
            'settings' => $settings,
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function download(Enrollment $invoice)
    {
        $user = auth()->user();
        $guardian = Guardian::where('user_id', $user->id)->firstOrFail();
        $studentIds = $guardian->children()->pluck('students.id');

        // Verify guardian owns this student
        if (! $studentIds->contains($invoice->student_id)) {
            abort(404);
        }

        // Load relationships
        $invoice->load(['student', 'guardian', 'schoolYear']);

        // Get payments for this enrollment
        $payments = Payment::where('invoice_id', $invoice->id)
            ->orderBy('payment_date', 'asc')
            ->get();

        // Get school settings
        $settings = Setting::pluck('value', 'key');

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', [
            'enrollment' => $invoice,
            'payments' => $payments,
            'settings' => $settings,
            'invoiceDate' => now()->format('F d, Y'),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("invoice-{$invoice->enrollment_id}.pdf");
    }
}
