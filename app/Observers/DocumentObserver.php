<?php
namespace App\Observers;

use App\Models\Document;
use App\Services\AuditLogger;

class DocumentObserver
{
    public function created(Document $document): void
    {
        AuditLogger::log('document_uploaded', $document, [], [
            'title'    => $document->title,
            'case_id'  => $document->case_id,
            'category' => $document->category,
            'version'  => $document->version,
        ]);
    }

    public function updated(Document $document): void
    {
        AuditLogger::log(
            'document_updated',
            $document,
            $document->getOriginal(),
            $document->getChanges()
        );
    }

    public function deleted(Document $document): void
    {
        AuditLogger::log('document_deleted', $document, [
            'title'   => $document->title,
            'case_id' => $document->case_id,
        ]);
    }
}