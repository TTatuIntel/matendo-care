<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->paginate(20);
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'file' => 'required|file',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('documents');

        $document = Document::create([
            'user_id' => auth()->id(),
            'patient_id' => $validated['patient_id'],
            'filename' => basename($path),
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'uploaded_by' => auth()->id(),
            'uploader_name' => auth()->user()->name,
        ]);

        return redirect()->route('documents.show', $document)->with('success', 'Document uploaded.');
    }

    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $document->update($validated);

        return redirect()->route('documents.show', $document)->with('success', 'Document updated.');
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted.');
    }

    public function preview(Document $document)
    {
        return response()->file(storage_path('app/' . $document->path));
    }

    public function download(Document $document)
    {
        return Storage::download($document->path, $document->original_filename);
    }

    public function view(Document $document)
    {
        return response()->file(storage_path('app/' . $document->path));
    }

    public function verify(Document $document)
    {
        $document->markAsVerified();
        return back()->with('success', 'Document verified.');
    }

    public function adminIndex()
    {
        $documents = Document::latest()->paginate(20);
        return view('admin.documents.index', compact('documents'));
    }

    public function doctorIndex()
    {
        $documents = Document::latest()->paginate(20);
        return view('doctor.documents.index', compact('documents'));
    }
}
