<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $enrollmentsQuery = Enrollment::query();

        $statusCounts = [
            'all' => (clone $enrollmentsQuery)->count(),
            'pending' => (clone $enrollmentsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $enrollmentsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $enrollmentsQuery)->where('status', 'rejected')->count(),
            'enrolled' => (clone $enrollmentsQuery)->where('status', 'enrolled')->count(),
            'completed' => (clone $enrollmentsQuery)->where('status', 'completed')->count(),
        ];

        $enrollments = $enrollmentsQuery->with(['student'])
            ->when($request->input('search'), function ($query, $search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->when($request->input('grade'), function ($query, $grade) {
                $query->where('grade_level', $grade);
            })
            ->latest()
            ->paginate(2)
            ->withQueryString();

        return Inertia::render('admin/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['search', 'status', 'grade']),
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('admin/enrollments/show', [
            'enrollment' => [
                'id' => $id,
                'student_name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'pending',
                'submitted_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('admin/enrollments/edit', [
            'enrollment' => [
                'id' => $id,
                'student_name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'pending',
            ],
        ]);
    }
}
