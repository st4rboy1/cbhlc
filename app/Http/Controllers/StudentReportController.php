<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentReportController extends Controller
{
    /**
     * Display the report for a specific student.
     */
    public function show(Request $request, Student $student): Response
    {
        $user = $request->user();

        // Authorization check
        if ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Admin users can see any student report
        } elseif ($user->hasRole('guardian')) {
            // Guardians can only see their children's reports
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('students.id');
                if (! $studentIds->contains($student->id)) {
                    abort(403, 'You do not have permission to view this student report.');
                }
            } else {
                abort(403, 'Guardian profile not found.');
            }
        } elseif ($user->hasRole('student')) {
            // Students can only see their own report
            if ($student->user_id !== $user->id) {
                abort(403, 'You can only view your own report.');
            }
        } else {
            abort(403, 'You do not have permission to view student reports.');
        }

        // Load any additional data needed for the report
        $student->load(['enrollments', 'guardians']);

        return Inertia::render('shared/studentreport', [
            'student' => $student,
        ]);
    }
}
