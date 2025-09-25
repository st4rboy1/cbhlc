<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
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

        return Inertia::render('shared/invoice', [
            'enrollment' => $invoice,
            'invoiceNumber' => $invoice->enrollment_id ?? 'No Invoice Available',
            'currentDate' => now()->format('F d, Y'),
        ]);
    }

    /**
     * Display a listing of invoices for the authenticated user
     * For now, shows the latest invoice (can be expanded to show all invoices)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $enrollment = null;

        if ($user->hasRole('guardian')) {
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('students.id');
                $enrollment = Enrollment::with(['student', 'guardian'])
                    ->whereIn('student_id', $studentIds)
                    ->latest()
                    ->first();
            }
        } elseif ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // For admin users, show the most recent enrollment overall
            $enrollment = Enrollment::with(['student', 'guardian'])
                ->latest()
                ->first();
        }

        if (! $enrollment) {
            return Inertia::render('shared/invoice', [
                'enrollment' => null,
                'invoiceNumber' => 'No Invoice Available',
                'currentDate' => now()->format('F d, Y'),
            ]);
        }

        return Inertia::render('shared/invoice', [
            'enrollment' => $enrollment,
            'invoiceNumber' => $enrollment->enrollment_id ?? 'No Invoice Available',
            'currentDate' => now()->format('F d, Y'),
        ]);
    }
}
