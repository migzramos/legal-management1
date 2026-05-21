<?php
namespace App\Observers;

use App\Models\LegalCase;
use App\Services\AuditLogger;

class CaseObserver
{
    public function created(LegalCase $case): void
    {
        AuditLogger::log('case_created', $case, [], $case->toArray());
    }

    public function updated(LegalCase $case): void
    {
        AuditLogger::log(
            'case_updated',
            $case,
            $case->getOriginal(),
            $case->getChanges()
        );
    }

    public function deleted(LegalCase $case): void
    {
        AuditLogger::log('case_deleted', $case, $case->toArray());
    }

    public function restored(LegalCase $case): void
    {
        AuditLogger::log('case_restored', $case, [], $case->toArray());
    }
}