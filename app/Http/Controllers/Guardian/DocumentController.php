<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Student $student): InertiaResponse
    {
        $this->authorize('view', $student);

        $documents = $student->documents()
            ->with('verifiedBy:id,name')
            ->latest('upload_date')
            ->get();

        return Inertia::render('guardian/students/documents/index', [
            'student' => $student->only(['id', 'first_name', 'middle_name', 'last_name', 'student_id']),
            'documents' => $documents,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request, Student $student): JsonResponse
    {
        // Authorize user can upload documents for this student
        $this->authorize('uploadDocument', [Document::class, $student]);

        try {
            // Handle file upload
            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $storedName = Str::random(40).'.'.$file->extension();

            // Store file (no 'documents/' prefix since disk root is already storage/app/documents)
            $path = $file->storeAs(
                (string) $student->id,
                $storedName,
                'private'
            );

            // Create database record
            $document = $student->documents()->create([
                'document_type' => $request->document_type,
                'original_filename' => $originalName,
                'stored_filename' => $storedName,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'upload_date' => now(),
                'verification_status' => VerificationStatus::PENDING,
            ]);

            return response()->json([
                'message' => 'Document uploaded successfully',
                'document' => $document->load('student:id,first_name,last_name'),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Document upload failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to upload document. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        // Ensure document belongs to student
        if ($document->student_id !== $student->id) {
            return response()->json([
                'message' => 'Document not found for this student.',
            ], 404);
        }

        // Generate signed URL valid for 5 minutes
        $url = URL::temporarySignedRoute(
            'guardian.students.documents.download',
            now()->addMinutes(5),
            ['student' => $student->id, 'document' => $document->id]
        );

        // Log document access
        activity()
            ->performedOn($document)
            ->withProperties([
                'action' => 'viewed',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Document accessed');

        return response()->json([
            'document' => $document->load(['student:id,first_name,last_name', 'verifiedBy:id,name']),
            'url' => $url,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student, Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        // Ensure document belongs to student
        if ($document->student_id !== $student->id) {
            return response()->json([
                'message' => 'Document not found for this student.',
            ], 404);
        }

        try {
            // Delete physical file
            if (Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }

            $document->delete();

            return response()->json([
                'message' => 'Document deleted successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Document deletion failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to delete document. Please try again.',
            ], 500);
        }
    }

    /**
     * Download the document with signed URL verification.
     */
    public function download(Request $request, Student $student, Document $document): StreamedResponse|JsonResponse
    {
        // Verify signed URL
        if (! $request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid or expired download link.',
            ], 403);
        }

        $this->authorize('download', $document);

        // Ensure document belongs to student
        if ($document->student_id !== $student->id) {
            return response()->json([
                'message' => 'Document not found for this student.',
            ], 404);
        }

        // Log document download
        activity()
            ->performedOn($document)
            ->withProperties([
                'action' => 'downloaded',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Document downloaded');

        return Storage::disk('private')->download(
            $document->file_path,
            $document->original_filename
        );
    }
}
