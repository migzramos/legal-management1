<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Mail\CaseStatusUpdated;
use App\Models\LegalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class CaseController extends Controller
{
    public function index()
    {
        $cases = LegalCase::with([
                'client:id,name,email,phone',
                'category:id,name',
                'courtType:id,name',
            ])
            ->where('lawyer_id', auth()->id())
            ->latest()
            ->paginate(15);

        $documents = \App\Models\Document::with('case:id,title')
            ->whereHas('case', fn($q) => $q->where('lawyer_id', auth()->id()))
            ->latest()
            ->paginate(15);

        return view('lawyer.case.index', compact('cases', 'documents'));
    }

    public function create()
    {
        $categories = \App\Models\CaseCategory::where('is_active', true)->get();
        $courtTypes = \App\Models\CourtType::where('is_active', true)->get();
        $clients    = \App\Models\User::where('role', 'client')->get();

        return view('lawyer.case.case-create', compact('categories', 'courtTypes', 'clients'));
    }

    public function store(StoreCaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $case = LegalCase::create([
            'title'          => $validated['title'],
            'description'    => $validated['description'] ?? null,
            'category_id'    => $validated['case_category_id'],
            'court_type_id'  => $validated['court_type_id'],
            'client_id'      => $validated['client_id'],
            'status'         => $validated['status'] ?? 'intake',
            'filed_date'     => $validated['filing_date'] ?? null,
            'hearing_date'   => $validated['hearing_date'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'lawyer_id'      => auth()->id(),
            'case_number'    => 'CASE-' . strtoupper(uniqid()),
        ]);

        $this->auditLog('created_case', $case);

        return redirect()
            ->route('lawyer.cases.show', $case)
            ->with('success', 'Case created successfully.');
    }

    public function show(LegalCase $case)
{
    $this->authorize('view', $case);

    $case->load([
        'client:id,name,email,phone',
        'category:id,name',
        'courtType:id,name',
        'documents',
        'tasks',
        'schedules',
        'timeEntries',
        'invoices',
        'messages',
    ]);

    return view('lawyer.case.show', compact('case'));
}
    public function edit(LegalCase $case)
    {
        $this->authorize('update', $case);

        $categories = \App\Models\CaseCategory::where('is_active', true)->get();
        $courtTypes = \App\Models\CourtType::where('is_active', true)->get();
        $clients    = \App\Models\User::where('role', 'client')->get();

        return view('lawyer.case.case-edit', compact('case', 'categories', 'courtTypes', 'clients'));
    }

    public function update(UpdateCaseRequest $request, LegalCase $case): RedirectResponse
    {
        $this->authorize('update', $case);

        $old = $case->toArray();
        $case->update($request->validated());

        if (($old['status'] ?? null) !== $case->status) {
            $case->loadMissing(['client:id,name,email', 'lawyer:id,name,email']);
            if ($case->client?->email) {
                Mail::to($case->client->email)
                    ->queue(new CaseStatusUpdated($case, $old['status'] ?? 'unknown', $case->status));
            }
        }

        $this->auditLog('updated_case', $case, $old, $case->fresh()->toArray());

        return redirect()
            ->route('lawyer.cases.show', $case)
            ->with('success', 'Case updated successfully.');
    }

        public function destroy(LegalCase $case): RedirectResponse
    {
        $this->authorize('delete', $case);

        $this->auditLog('deleted_case', $case);
        $case->delete();

        return redirect()
            ->route('lawyer.cases.index')
            ->with('success', 'Case deleted successfully.');
    }

    public function updateStatus(LegalCase $case, string $status): RedirectResponse
    {
        $this->authorize('update', $case);

        $allowed = [
            'intake',
            'barangay_mediation',
            'escalation_to_court',
            'active_case',
            'resolution',
        ];

        if (!in_array($status, $allowed)) {
            return back()->withErrors(['status' => 'Invalid status.']);
        }

        $old = $case->status;

        $case->update([
            'status'      => $status,
            'closed_date' => $status === 'resolution' ? now() : null,
        ]);

        if ($old !== $case->status) {
            $case->loadMissing(['client:id,name,email', 'lawyer:id,name,email']);
            if ($case->client?->email) {
                Mail::to($case->client->email)
                    ->queue(new CaseStatusUpdated($case, $old, $case->status));
            }
        }

        $this->auditLog("case_status_changed_to_{$status}", $case);

        return back()->with('success', "Case status updated to {$status}.");
    }
}