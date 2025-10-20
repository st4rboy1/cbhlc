<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function indexAll()
    {
        $guardian = Auth::user()->guardian;
        $students = $guardian->children;
        $documents = Document::whereIn('student_id', $students->pluck('id'))->with('student')->latest()->paginate(10);

        return Inertia::render('guardian/documents/index', [
            'documents' => $documents,
        ]);
    }

    public function index(Student $student)
    {
        $this->authorize('view', $student);

        $documents = $student->documents()->latest()->paginate(10);

        return Inertia::render('guardian/students/documents/index', [
            'student' => $student,
            'documents' => $documents,
        ]);
    }

    public function store(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $request->validate([
            'documents' => 'required|array',
            'documents.*.file' => 'required|file|mimes:pdf,jpg,png|max:2048',
            'documents.*.type' => 'required|string',
        ]);

        foreach ($request->file('documents') as $documentData) {
            $student->addDocument($documentData['file'], $documentData['type']);
        }

        return back()->with('success', 'Documents uploaded successfully.');
    }

    public function show(Student $student, Document $document)
    {
        $this->authorize('view', $student);

        return Inertia::render('guardian/students/documents/show', [
            'student' => $student,
            'document' => $document,
        ]);
    }

    public function download(Student $student, Document $document)
    {
        $this->authorize('view', $student);

        return $document->download();
    }

    public function destroy(Student $student, Document $document)
    {
        $this->authorize('update', $student);

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
