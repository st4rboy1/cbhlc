<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateNotificationPreferencesRequest;
use App\Models\NotificationPreference;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    /**
     * Display notification preferences
     */
    public function index(): Response
    {
        $preferences = auth()->user()->notificationPreferences()
            ->get()
            ->keyBy('notification_type');

        $availableTypes = NotificationPreference::availableTypes();

        return Inertia::render('settings/notifications', [
            'preferences' => $preferences,
            'availableTypes' => $availableTypes,
        ]);
    }

    /**
     * Update notification preferences
     */
    public function update(UpdateNotificationPreferencesRequest $request)
    {
        $user = auth()->user();

        foreach ($request->preferences as $type => $settings) {
            $user->setNotificationPreference(
                $type,
                $settings['email_enabled'] ?? false,
                $settings['database_enabled'] ?? false
            );
        }

        activity()
            ->causedBy($user)
            ->log('Notification preferences updated');

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    /**
     * Reset notification preferences to default
     */
    public function reset()
    {
        $user = auth()->user();

        // Delete all preferences (will use defaults)
        $user->notificationPreferences()->delete();

        // Recreate defaults
        $user->createDefaultNotificationPreferences();

        activity()
            ->causedBy($user)
            ->log('Notification preferences reset to default');

        return back()->with('success', 'Notification preferences reset to default.');
    }
}
