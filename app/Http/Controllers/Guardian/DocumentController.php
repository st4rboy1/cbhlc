<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        $documents = $student->documents()
            ->with('verifiedBy:id,name')
            ->latest('upload_date')
            ->get();

        return response()->json([
            'documents' => $documents,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request, Student $student): JsonResponse
    {
        // Authorize guardian owns student
        $this->authorize('update', $student);

        try {
            // Handle file upload
            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $storedName = Str::random(40).'.'.$file->extension();

            // Store file
            $path = $file->storeAs(
                "documents/{$student->id}",
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
        $this->authorize('view', $student);

        // Ensure document belongs to student
        if ($document->student_id !== $student->id) {
            return response()->json([
                'message' => 'Document not found for this student.',
            ], 404);
        }

        return response()->json([
            'document' => $document->load(['student:id,first_name,last_name', 'verifiedBy:id,name']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student, Document $document): JsonResponse
    {
        $this->authorize('update', $student);

        // Ensure document belongs to student
        if ($document->student_id !== $student->id) {
            return response()->json([
                'message' => 'Document not found for this student.',
            ], 404);
        }

        // Only allow deletion if document is pending or rejected
        if ($document->verification_status === VerificationStatus::VERIFIED) {
            return response()->json([
                'message' => 'Cannot delete a verified document.',
            ], 403);
        }

        try {
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
}
