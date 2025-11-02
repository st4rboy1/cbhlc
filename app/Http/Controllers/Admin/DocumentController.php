<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function pending()
    {
        return Inertia::render('admin/documents/pending');
    }

    /**
     * Download the specified document file.
     */
    public function download(Document $document)
    {
        $this->authorize('download', $document);

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
}
