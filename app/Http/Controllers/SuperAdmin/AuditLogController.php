<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs with filters
     */
    public function index(Request $request): Response
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Apply filters
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', "%{$request->description}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50)->withQueryString();

        // Get unique subject types for filter dropdown
        $subjectTypes = Activity::distinct()
            ->pluck('subject_type')
            ->filter()
            ->values();

        // Get users who have caused activities for filter dropdown
        $causers = Activity::distinct()
            ->whereNotNull('causer_id')
            ->with('causer:id,name')
            ->get()
            ->pluck('causer')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return Inertia::render('super-admin/audit-logs/index', [
            'activities' => $activities,
            'filters' => $request->only(['causer_id', 'subject_type', 'log_name', 'description', 'date_from', 'date_to']),
            'causers' => $causers,
            'subjectTypes' => $subjectTypes,
        ]);
    }

    /**
     * Display the specified audit log with related activities
     */
    public function show(Activity $activity): Response
    {
        $activity->load(['causer', 'subject']);

        // Get related activities for the same subject
        $relatedActivities = Activity::query()
            ->where('subject_type', $activity->subject_type)
            ->where('subject_id', $activity->subject_id)
            ->where('id', '!=', $activity->id)
            ->with('causer')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('super-admin/audit-logs/show', [
            'activity' => $activity,
            'relatedActivities' => $relatedActivities,
        ]);
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        // Apply same filters as index
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', "%{$request->description}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->get();

        $filename = 'audit-log-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Timestamp',
                'User',
                'User Email',
                'Action',
                'Model Type',
                'Model ID',
                'Log Name',
                'Properties',
            ]);

            // CSV rows
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->created_at->toDateTimeString(),
                    $activity->causer?->name ?? 'System',
                    $activity->causer?->email ?? '',
                    $activity->description,
                    $activity->subject_type ?? '',
                    $activity->subject_id ?? '',
                    $activity->log_name,
                    json_encode($activity->properties),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
