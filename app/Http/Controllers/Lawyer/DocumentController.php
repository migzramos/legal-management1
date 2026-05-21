<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\LegalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(LegalCase $case): JsonResponse
    {
        $this->authorize('view', $case);

        $documents = Document::where('case_id', $case->id)
            ->whereNull('parent_document_id')
            ->with('versions', 'uploader:id,name')
            ->latest()
            ->get();

        return response()->json($documents);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $file    = $request->file('file');
        $case    = LegalCase::findOrFail($request->case_id);

        $this->authorize('update', $case);

        // Handle versioning
        $version = 1;
        if ($request->parent_document_id) {
            $parent  = Document::findOrFail($request->parent_document_id);
            $version = $parent->versions()->max('version') + 1;
        }

        $path = $file->store("cases/{$case->id}/documents", 'local');

        $document = Document::create([
            'case_id'              => $case->id,
            'uploaded_by'          => auth()->id(),
            'title'                => $request->title,
            'file_name'            => $file->getClientOriginalName(),
            'file_path'            => $path,
            'file_type'            => $file->getClientMimeType(),
            'file_size'            => $file->getSize(),
            'category'             => $request->category,
            'version'              => $version,
            'parent_document_id'   => $request->parent_document_id,
            'is_visible_to_client' => $request->boolean('is_visible_to_client'),
            'notes'                => $request->notes,
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'uploaded_document',
            'model_type' => Document::class,
            'model_id'   => $document->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'message'  => 'Document uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);
        $document->load(['uploader:id,name', 'versions', 'parentDocument']);
        return response()->json($document);
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'deleted_document',
            'model_type' => Document::class,
            'model_id'   => $document->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    public function toggleVisibility(Document $document)
    {
        $this->authorize('update', $document);

        $document->update([
            'is_visible_to_client' => !$document->is_visible_to_client,
        ]);

        $status = $document->is_visible_to_client ? 'visible' : 'hidden';

        return redirect()->back()->with('success', "Document is now {$status} to client.");
    }
}