<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreInvoiceRequest;
use App\Http\Requests\SuperAdmin\UpdateInvoiceRequest;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of the resource.
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

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('super-admin/invoices/index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'from_date', 'to_date']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Invoice::class);

        $enrollments = Enrollment::with(['student', 'guardian.user'])
            ->where('status', 'approved')
            ->get();

        return Inertia::render('super-admin/invoices/create', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $invoice = $this->invoiceService->createInvoice($validated);
        });

        return redirect()->route('super-admin.invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['enrollment.student', 'enrollment.guardian.user', 'items', 'payments']);

        return Inertia::render('super-admin/invoices/show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $invoice->load(['enrollment', 'items']);
        $enrollments = Enrollment::with(['student', 'guardian.user'])
            ->where('status', 'approved')
            ->get();

        return Inertia::render('super-admin/invoices/edit', [
            'invoice' => $invoice,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $invoice) {
            $invoice->update([
                'enrollment_id' => $validated['enrollment_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
            ]);

            // Handle invoice items
            $existingItemIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete removed items
            $invoice->items()
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            // Update or create items
            foreach ($validated['items'] as $item) {
                if (isset($item['id'])) {
                    InvoiceItem::where('id', $item['id'])
                        ->update([
                            'description' => $item['description'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'amount' => $item['amount'],
                        ]);
                } else {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            // Recalculate totals
            $this->invoiceService->recalculateTotals($invoice);
        });

        return redirect()->route('super-admin.invoices.index')
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        // Check if invoice has payments
        if ($invoice->payments()->exists()) {
            return redirect()->route('super-admin.invoices.index')
                ->with('error', 'Cannot delete invoice with existing payments.');
        }

        $invoice->delete();

        return redirect()->route('super-admin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}