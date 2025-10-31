<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
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
    public function show(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->firstOrFail();
        $studentIds = $guardian->children()->pluck('students.id');

        // Load the enrollment and items relationship
        $invoice->load('enrollment.student', 'enrollment.guardian', 'enrollment.schoolYear', 'items');
        $enrollment = $invoice->enrollment;

        // Verify guardian owns this student
        if (! $enrollment || ! $studentIds->contains($enrollment->student_id)) {
            abort(404);  // Return 404 for security
        }

        // Get settings
        $settings = Setting::pluck('value', 'key');

        return Inertia::render('shared/invoice', [
            'invoice' => $invoice,
            'invoiceNumber' => $invoice->invoice_number ?? 'No Invoice Available',
            'currentDate' => now()->format('F d, Y'),
            'settings' => $settings,
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function download(Invoice $invoice)
    {
        $user = auth()->user();
        $guardian = Guardian::where('user_id', $user->id)->firstOrFail();
        $studentIds = $guardian->children()->pluck('students.id');

        // Load the enrollment relationship
        $invoice->load('enrollment.student', 'enrollment.guardian', 'enrollment.schoolYear');
        $enrollment = $invoice->enrollment;

        // Verify guardian owns this student
        if (! $enrollment || ! $studentIds->contains($enrollment->student_id)) {
            abort(404);
        }

        // Get payments for this invoice
        $payments = Payment::where('invoice_id', $invoice->id)
            ->orderBy('payment_date', 'asc')
            ->get();

        // Get school settings
        $settings = Setting::pluck('value', 'key');

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', [
            'enrollment' => $enrollment,
            'payments' => $payments,
            'settings' => $settings,
            'invoiceDate' => now()->format('F d, Y'),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
