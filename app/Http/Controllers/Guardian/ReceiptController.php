<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Receipt::class);

        $query = Receipt::whereHas('payment.invoice.enrollment', function ($query) {
            $query->where('guardian_id', auth()->user()->guardian->id);
        })->with(['payment.invoice.enrollment.student', 'invoice.enrollment.student', 'receivedBy']);

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

        return Inertia::render('guardian/receipts/index', [
            'receipts' => $receipts,
            'filters' => $request->only(['search', 'payment_method', 'date_from', 'date_to']),
        ]);
    }

    public function show(Receipt $receipt)
    {
        $receipt->load(['payment.invoice.enrollment.student', 'invoice.enrollment.student', 'receivedBy']);

        Gate::authorize('view', $receipt);

        return Inertia::render('guardian/receipts/show', [
            'receipt' => $receipt,
        ]);
    }
}
