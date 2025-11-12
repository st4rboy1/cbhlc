<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of all invoices (admin can view all)
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $query = Invoice::with(['enrollment.student', 'enrollment.guardian.user', 'items']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('enrollment.student', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->get('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->get('to_date'));
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        return Inertia::render('admin/invoices/index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'from_date', 'to_date']),
        ]);
    }

    /**
     * Display the invoice for a specific enrollment (admin can view any)
     */
    public function show(Request $request, Enrollment $invoice)
    {
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
     * Download invoice as PDF (admin can download any)
     */
    public function download(Enrollment $invoice)
    {
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
