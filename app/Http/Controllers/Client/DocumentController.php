<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // View documents visible to client
    public function index(LegalCase $case)
    {
        $this->authorize('view', $case);

        $documents = Document::where('case_id', $case->id)
            ->where('is_visible_to_client', true)
            ->with('uploader:id,name')
            ->latest()
            ->get();

        return view('client.documents', compact('case', 'documents'));
    }

    // Client uploads a requested document
    public function upload(Request $request, LegalCase $case)
    {
        $this->authorize('view', $case);

        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|max:20480',
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store("cases/{$case->id}/client-uploads", 'local');

        $document = Document::create([
            'case_id'              => $case->id,
            'uploaded_by'          => auth()->id(),
            'title'                => $request->title,
            'file_name'            => $file->getClientOriginalName(),
            'file_path'            => $path,
            'file_type'            => $file->getClientMimeType(),
            'file_size'            => $file->getSize(),
            'category'             => 'requested',
            'version'              => 1,
            'is_visible_to_client' => true,
            'notes'                => $request->notes,
        ]);

        return redirect()->route('client.documents.index', $case->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        $document->load(['uploader:id,name', 'case:id,case_number,title']);

        return view('client.document-detail', compact('document'));
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }
}