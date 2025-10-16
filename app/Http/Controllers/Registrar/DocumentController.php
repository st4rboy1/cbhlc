<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registrar\RejectDocumentRequest;
use App\Models\Document;
use App\Notifications\DocumentRejectedNotification;
use App\Notifications\DocumentVerifiedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of pending documents.
     */
    public function pending(Request $request)
    {
        $documents = Document::with(['student', 'verifiedBy'])
            ->where('verification_status', 'pending')
            ->when($request->student_id, fn ($query) => $query->where('student_id', $request->student_id))
            ->latest('upload_date')
            ->paginate(20);

        return Inertia::render('registrar/documents/pending', [
            'documents' => $documents,
        ]);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document);

        $document->load(['student.guardians', 'verifiedBy']);

        // Generate temporary signed URL for file viewing
        $url = Storage::disk('private')->temporaryUrl(
            $document->file_path,
            now()->addMinutes(5)
        );

        return response()->json([
            'document' => $document,
            'url' => $url,
        ]);
    }

    /**
     * Verify the specified document.
     */
    public function verify(Document $document)
    {
        $this->authorize('verify', $document);

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

        return back()->with('success', 'Document verified successfully.');
    }

    /**
     * Reject the specified document.
     */
    public function reject(RejectDocumentRequest $request, Document $document)
    {
        $this->authorize('reject', $document);

        $document->reject(auth()->user(), $request->notes);

        // Log activity
        activity()
            ->performedOn($document)
            ->withProperties([
                'document_type' => $document->document_type,
                'student_id' => $document->student_id,
                'rejection_reason' => $request->notes,
            ])
            ->log('Document rejected');

        // Notify all guardians of the student
        $document->student->guardians->each(function ($guardian) use ($document) {
            $guardian->user?->notify(new DocumentRejectedNotification($document));
        });

        return back()->with('success', 'Document rejected.');
    }
}
