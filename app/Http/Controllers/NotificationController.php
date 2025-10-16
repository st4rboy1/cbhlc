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
     */
    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
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
