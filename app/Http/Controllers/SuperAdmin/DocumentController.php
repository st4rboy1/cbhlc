<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\RejectDocumentRequest;
use App\Models\Document;
use App\Models\Student;
use App\Notifications\DocumentRejectedNotification;
use App\Notifications\DocumentVerifiedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of all documents.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Document::class);

        $studentsQuery = Student::with(['documents' => function ($query) use ($request) {
            if ($request->filled('verification_status')) {
                $query->where('verification_status', $request->get('verification_status'));
            }
            if ($request->filled('document_type')) {
                $query->where('document_type', $request->get('document_type'));
            }
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('original_filename', 'like', "%{$search}%");
                });
            }
        }, 'documents.verifiedBy']);

        if ($request->filled('student_id')) {
            $studentsQuery->where('id', $request->get('student_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('documents', function ($documentQuery) use ($search) {
                        $documentQuery->where('original_filename', 'like', "%{$search}%");
                    });
            });
        }

        // Apply sorting to students
        if ($request->filled('sort_by') && $request->filled('sort_direction')) {
            $studentsQuery->orderBy($request->get('sort_by'), $request->get('sort_direction'));
        } else {
            $studentsQuery->latest('created_at'); // Default sort for students
        }

        $studentsWithDocuments = $studentsQuery->paginate(20)->withQueryString();

        $allStudents = Student::select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($student) {
                return [
                    'value' => $student->id,
                    'label' => "{$student->first_name} {$student->last_name}",
                ];
            });

        return Inertia::render('super-admin/documents/index', [
            'studentsWithDocuments' => $studentsWithDocuments,
            'filters' => $request->only(['verification_status', 'document_type', 'student_id', 'search', 'sort_by', 'sort_direction']),
            'students' => $allStudents,
        ]);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        Gate::authorize('view', $document);

        $document->load(['student.guardians', 'verifiedBy']);

        // Generate URL for file viewing using the view route
        $url = route('super-admin.documents.view', $document);

        return Inertia::render('super-admin/documents/show', [
            'document' => $document,
            'fileUrl' => $url,
        ]);
    }

    /**
     * View/download the specified document file.
     */
    public function view(Document $document)
    {
        Gate::authorize('view', $document);

        // Check if file exists
        if (! Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'Document file not found.');
        }

        // Return the file for viewing in browser
        return response()->file(
            Storage::disk('private')->path($document->file_path),
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="'.$document->original_filename.'"',
            ]
        );
    }

    /**
     * Download the specified document file.
     */
    public function download(Document $document)
    {
        Gate::authorize('download', $document);

        // Check if file exists
        if (! Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'Document file not found.');
        }

        // Log document download
        activity()
            ->performedOn($document)
            ->withProperties([
                'document_type' => $document->document_type,
                'student_id' => $document->student_id,
                'action' => 'downloaded',
            ])
            ->log('Document downloaded by '.auth()->user()->name);

        // Return the file for download
        return Storage::disk('private')->download($document->file_path, $document->original_filename);
    }

    /**
     * Verify the specified document.
     */
    public function verify(Document $document)
    {
        Gate::authorize('verify', $document);

        $document->verify(auth()->user());

        // Log activity
        activity()
            ->performedOn($document)
            ->withProperties([
                'document_type' => $document->document_type,
                'student_id' => $document->student_id,
            ])
            ->log('Document verified');

        // Notify all guardians of the student
        $document->student->guardians->each(function ($guardian) use ($document) {
            $guardian->user?->notify(new DocumentVerifiedNotification($document));
        });

        return redirect()->route('super-admin.documents.index')
            ->with('success', 'Document verified successfully.');
    }

    /**
     * Reject the specified document.
     */
    public function reject(RejectDocumentRequest $request, Document $document)
    {
        Gate::authorize('reject', $document);

        $document->reject(auth()->user(), $request->validated()['notes']);

        // Log activity
        activity()
            ->performedOn($document)
            ->withProperties([
                'document_type' => $document->document_type,
                'student_id' => $document->student->id,
                'rejection_reason' => $request->validated()['notes'],
            ])
            ->log('Document rejected');

        // Notify all guardians of the student
        $document->student->guardians->each(function ($guardian) use ($document) {
            $guardian->user?->notify(new DocumentRejectedNotification($document));
        });

        return redirect()->route('super-admin.documents.index')
            ->with('success', 'Document rejected.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);

        $documentData = $document->toArray();
        $document->delete();

        activity()
            ->withProperties($documentData)
            ->log('Document deleted');

        return redirect()->route('super-admin.documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
