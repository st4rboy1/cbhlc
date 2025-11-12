<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePaymentRequest;
use App\Http\Requests\SuperAdmin\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Payment::class);

        $query = Payment::with(['invoice.enrollment.student', 'invoice.enrollment.guardian.user', 'processedBy']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($iq) use ($search) {
                        $iq->where('invoice_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice.enrollment.student', function ($sq) use ($search) {
                        $sq->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->get('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->get('to_date'));
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return Inertia::render('admin/payments/index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'payment_method', 'status', 'from_date', 'to_date']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Payment::class);

        $invoices = Invoice::with(['enrollment.student', 'enrollment.guardian.user'])
            ->whereIn('status', ['sent', 'overdue', 'partial'])
            ->get();

        return Inertia::render('admin/payments/create', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        Gate::authorize('create', Payment::class);

        $validated = $request->validated();

        $payment = $this->paymentService->processPayment($validated);

        return redirect()->route('admin/payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        Gate::authorize('view', $payment);

        $payment->load([
            'invoice.enrollment.student',
            'invoice.enrollment.guardian.user',
            'invoice.items',
            'processedBy',
        ]);

        return Inertia::render('admin/payments/show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        Gate::authorize('update', $payment);

        $payment->load(['invoice.enrollment.student', 'invoice.enrollment.guardian.user']);
        $invoices = Invoice::with(['enrollment.student', 'enrollment.guardian.user'])
            ->whereIn('status', ['sent', 'paid', 'overdue'])
            ->get();

        return Inertia::render('admin/payments/edit', [
            'payment' => $payment,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        Gate::authorize('update', $payment);

        $validated = $request->validated();

        $payment->update($validated);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment updated successfully.');
    }
}
