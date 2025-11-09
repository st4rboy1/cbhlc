<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response; // Import Carbon

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
        $student->load(['enrollments.schoolYear', 'enrollments.enrollmentPeriod']);

        $latestEnrollment = $student->enrollments->sortByDesc('created_at')->first();

        $studentInfo = [
            'name' => $student->first_name.' '.$student->last_name,
            'age' => $student->birthdate ? Carbon::parse($student->birthdate)->age : null,
            'gender' => $student->gender,
            'section' => $latestEnrollment ? $latestEnrollment->section : 'N/A', // Assuming enrollment has a section
            'birthdate' => $student->birthdate ? Carbon::parse($student->birthdate)->format('F d, Y') : 'N/A',
            'address' => $student->address ?? 'N/A', // Assuming student has an address field
            'gradeLevel' => $latestEnrollment ? $latestEnrollment->grade_level->value : 'N/A',
        ];

        $reportData = [
            'schoolYear' => $latestEnrollment && $latestEnrollment->schoolYear ? $latestEnrollment->schoolYear->display_name : 'N/A',
            'semester' => $latestEnrollment && $latestEnrollment->quarter ? $latestEnrollment->quarter->name : 'N/A', // Changed to quarter
            'status' => $latestEnrollment ? $latestEnrollment->status : 'N/A',
            'enrollmentDate' => $latestEnrollment ? Carbon::parse($latestEnrollment->created_at)->format('F d, Y') : 'N/A',
        ];

        return Inertia::render('shared/studentreport', [
            'studentInfo' => $studentInfo,
            'reportData' => $reportData,
        ]);
    }
}
