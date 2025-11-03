<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display the invoice for a specific enrollment
     */
    public function show(Request $request, Enrollment $invoice)
    {
        $user = $request->user();

        // Check permissions
        if ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Admin users can see any invoice
            // No additional check needed
        } elseif ($user->hasRole('guardian')) {
            // Guardians can only see their children's invoices
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('students.id');
                if (! $studentIds->contains($invoice->student_id)) {
                    abort(404);  // Return 404 for security - don't reveal invoice exists
                }
            } else {
                abort(404, 'Guardian profile not found.');
            }
        } else {
            abort(403, 'You do not have permission to view invoices.');
        }

        // Load related data
        $invoice->load(['student', 'guardian']);
        $settings = Setting::pluck('value', 'key');

        return Inertia::render('shared/invoice', [
            'enrollment' => $invoice,
            'invoiceNumber' => $invoice->enrollment_id ?? 'No Invoice Available',
            'currentDate' => now()->format('F d, Y'),
            'settings' => $settings,
        ]);
    }

    /**
     * Display a listing of invoices for the authenticated user
     * For now, shows the latest invoice (can be expanded to show all invoices)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $enrollmentsQuery = Enrollment::with(['student', 'guardian']);

        if ($user->hasRole('guardian')) {
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('students.id');
                $enrollmentsQuery->whereIn('student_id', $studentIds);
            } else {
                // If no guardian profile, return empty enrollments
                $enrollmentsQuery->whereRaw('0 = 1'); // Effectively return no results
            }
        } elseif ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Admin users can see all enrollments
            // No additional filtering needed for the query
        }

        $invoices = $enrollmentsQuery->latest()->paginate(10);

        $settings = Setting::pluck('value', 'key');

        return Inertia::render('guardian/invoices/index', [
            'invoices' => $invoices,
            'settings' => $settings,
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function download(Enrollment $invoice)
    {
        $user = auth()->user();

        // Authorization check (same as show method)
        if ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Admin users can download any invoice
        } elseif ($user->hasRole('guardian')) {
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('students.id');
                if (! $studentIds->contains($invoice->student_id)) {
                    abort(404);
                }
            } else {
                abort(404, 'Guardian profile not found.');
            }
        } else {
            abort(403, 'You do not have permission to download invoices.');
        }

        // Load relationships
        $invoice->load(['student', 'guardian']);

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
