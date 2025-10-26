<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreReceiptRequest;
use App\Http\Requests\SuperAdmin\UpdateReceiptRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReceiptController extends Controller
{
    /**
     * Display a listing of receipts.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Receipt::class);

        $query = Receipt::with(['payment.invoice.enrollment.student', 'invoice.enrollment.student', 'receivedBy']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhereHas('payment.invoice.enrollment.student', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->get('date_to'));
        }

        $receipts = $query->latest('receipt_date')->paginate(20)->withQueryString();

        return Inertia::render('super-admin/receipts/index', [
            'receipts' => $receipts,
            'filters' => $request->only(['search', 'payment_method', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new receipt.
     */
    public function create()
    {
        Gate::authorize('create', Receipt::class);

        $payments = Payment::with('invoice.enrollment.student')->whereDoesntHave('receipt')->get();
        $invoices = Invoice::with('enrollment.student')->get();

        return Inertia::render('super-admin/receipts/create', [
            'payments' => $payments,
            'invoices' => $invoices,
            'nextReceiptNumber' => Receipt::generateReceiptNumber(),
        ]);
    }

    /**
     * Store a newly created receipt.
     */
    public function store(StoreReceiptRequest $request)
    {
        Gate::authorize('create', Receipt::class);

        $validated = $request->validated();
        $validated['received_by'] = auth()->id();
        $validated['receipt_number'] = Receipt::generateReceiptNumber();

        $receipt = Receipt::create($validated);

        activity()
            ->performedOn($receipt)
            ->withProperties($receipt->toArray())
            ->log('Receipt created');

        return redirect()->route('super-admin.receipts.index')
            ->with('success', 'Receipt created successfully.');
    }

    /**
     * Display the specified receipt.
     */
    public function show(Receipt $receipt)
    {
        Gate::authorize('view', $receipt);

        $receipt->load(['payment.invoice.enrollment.student', 'invoice.enrollment.student', 'receivedBy']);

        return Inertia::render('super-admin/receipts/show', [
            'receipt' => $receipt,
        ]);
    }

    /**
     * Show the form for editing the specified receipt.
     */
    public function edit(Receipt $receipt)
    {
        Gate::authorize('update', $receipt);

        $receipt->load(['payment', 'invoice']);
        $payments = Payment::with('invoice.enrollment.student')->get();
        $invoices = Invoice::with('enrollment.student')->get();

        return Inertia::render('super-admin/receipts/edit', [
            'receipt' => $receipt,
            'payments' => $payments,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Update the specified receipt.
     */
    public function update(UpdateReceiptRequest $request, Receipt $receipt)
    {
        Gate::authorize('update', $receipt);

        $validated = $request->validated();
        $oldData = $receipt->toArray();
        $receipt->update($validated);

        activity()
            ->performedOn($receipt)
            ->withProperties(['old' => $oldData, 'new' => $receipt->fresh()->toArray()])
            ->log('Receipt updated');

        return redirect()->route('super-admin.receipts.index')
            ->with('success', 'Receipt updated successfully.');
    }

    /**
     * Remove the specified receipt.
     */
    public function destroy(Receipt $receipt)
    {
        Gate::authorize('delete', $receipt);

        $receiptData = $receipt->toArray();
        $receipt->delete();

        activity()
            ->withProperties($receiptData)
            ->log('Receipt deleted');

        return redirect()->route('super-admin.receipts.index')
            ->with('success', 'Receipt deleted successfully.');
    }
}
