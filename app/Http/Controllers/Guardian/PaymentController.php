<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();
        $studentIds = $guardian->children()->pluck('students.id');

        $payments = Payment::with(['invoice.enrollment.student', 'receipt'])
            ->whereHas('invoice.enrollment', function ($query) use ($studentIds) {
                $query->whereIn('student_id', $studentIds);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('guardian/payments/index', [
            'payments' => $payments,
        ]);
    }
}
