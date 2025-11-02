<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Get paginated notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Support limit parameter for dropdown (recent 5)
        $perPage = $request->input('limit', 20);

        $notifications = $query->latest()->paginate($perPage);

        return response()->json($notifications);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a specific notification as read.
     * Returns JSON for API calls, Inertia redirect for web calls.
     */
    public function markAsRead(string $id, Request $request)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // If this is an API call (starts with /api/), return JSON
        if ($request->is('api/*')) {
            return response()->json(['success' => true]);
        }

        // For Inertia calls, determine destination and redirect
        $route = $this->getNotificationRoute($notification);

        return redirect($route);
    }

    /**
     * Get the route to navigate to based on notification type.
     */
    private function getNotificationRoute($notification): string
    {
        $type = $notification->type;
        $data = $notification->data;

        // Document notifications - navigate to student documents page
        if (str_contains($type, 'DocumentRejected') || str_contains($type, 'DocumentVerified')) {
            if (isset($data['student_id'])) {
                return route('guardian.students.documents.index', ['student' => $data['student_id']]);
            }
        }

        // Enrollment notifications - navigate to enrollment details or list
        if (str_contains($type, 'EnrollmentApproved')) {
            if (isset($data['enrollment_id'])) {
                return route('guardian.billing.show', ['enrollment' => $data['enrollment_id']]);
            }
        }

        if (str_contains($type, 'EnrollmentRejected') || str_contains($type, 'EnrollmentSubmitted')) {
            if (isset($data['enrollment_id'])) {
                return route('guardian.enrollments.show', ['enrollment' => $data['enrollment_id']]);
            }

            return route('guardian.enrollments.index');
        }

        // New enrollment for review (Registrar)
        if (str_contains($type, 'NewEnrollmentForReview')) {
            return route('registrar.enrollments.index');
        }

        // New user registered (Super Admin/Admin)
        if (str_contains($type, 'NewUserRegistered') || str_contains($type, 'UserEmailVerified')) {
            if (isset($data['user_id'])) {
                return route('super-admin.users.show', ['user' => $data['user_id']]);
            }

            return route('super-admin.users.index');
        }

        // Payment and Invoice notifications - navigate to invoice details
        if (str_contains($type, 'PaymentReceived') || str_contains($type, 'InvoiceCreated')) {
            if (isset($data['invoice_id'])) {
                return route('guardian.invoices.show', ['invoice' => $data['invoice_id']]);
            }

            return route('guardian.invoices.index');
        }

        // Enrollment period status changed
        if (str_contains($type, 'EnrollmentPeriodStatus')) {
            return route('guardian.dashboard');
        }

        // Default fallback - return to dashboard based on user role
        $user = auth()->user();
        if ($user->hasRole('guardian')) {
            return route('guardian.dashboard');
        } elseif ($user->hasRole('registrar')) {
            return route('registrar.dashboard');
        } elseif ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return route('admin.dashboard');
        }

        return route('dashboard');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete all notifications for the authenticated user.
     */
    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Display the full notifications page.
     */
    public function page(Request $request): Response
    {
        $query = auth()->user()->notifications();

        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->latest()->paginate(20);

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
            'filter' => $request->filter ?? 'all',
        ]);
    }
}
