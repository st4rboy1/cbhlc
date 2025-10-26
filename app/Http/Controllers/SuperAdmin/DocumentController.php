<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\RejectDocumentRequest;
use App\Models\Document;
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

        $query = Document::with(['student', 'verifiedBy']);

        // Apply filters
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->get('verification_status'));
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->get('student_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $documents = $query->latest('upload_date')->paginate(20)->withQueryString();

        return Inertia::render('super-admin/documents/index', [
            'documents' => $documents,
            'filters' => $request->only(['verification_status', 'document_type', 'student_id', 'search']),
        ]);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        Gate::authorize('view', $document);

        $document->load(['student.guardians', 'verifiedBy']);

        // Generate temporary signed URL for file viewing
        $url = Storage::disk('private')->temporaryUrl(
            $document->file_path,
            now()->addMinutes(5)
        );

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

        // Return the file for viewing in browser
        return Storage::disk('private')->response($document->file_path, $document->original_filename);
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
                'student_id' => $document->student_id,
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
