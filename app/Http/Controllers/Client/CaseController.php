<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Mail\CaseStatusUpdated;
use App\Models\LegalCase;
use Illuminate\Http\JsonResponse;
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

  return view('client.cases', compact('cases', 'documents'));
    }

    public function create()
    {
        $categories = \App\Models\CaseCategory::where('is_active', true)->get();
        $courtTypes = \App\Models\CourtType::where('is_active', true)->get();
        $clients    = \App\Models\User::where('role', 'client')->get();

        return view('lawyer.case-create', compact('categories', 'courtTypes', 'clients'));
    }

    // FIX BUG 1: Use inherited auditLog() from base Controller instead of private log()
    public function store(StoreCaseRequest $request): JsonResponse
    {
        $case = LegalCase::create([
            ...$request->validated(),
            'lawyer_id' => auth()->id(),
        ]);

        $this->auditLog('created_case', $case);

        return response()->json([
            'message' => 'Case created successfully.',
            'case'    => $case->load(['client', 'category', 'courtType']),
        ], 201);
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
            // FIX BUG 3: Removed 'appointments.client' eager load — appointments()
            // relation was removed from LegalCase because case_id no longer exists
            // on the appointments table (dropped in migration 2026_04_28_000003).
        ]);

        return view('lawyer.case-detail', compact('case'));
    }

    public function edit(LegalCase $case)
    {
        $this->authorize('update', $case);

        $categories = \App\Models\CaseCategory::where('is_active', true)->get();
        $courtTypes = \App\Models\CourtType::where('is_active', true)->get();
        $clients    = \App\Models\User::where('role', 'client')->get();

        return view('lawyer.case-edit', compact('case', 'categories', 'courtTypes', 'clients'));
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

    public function destroy(LegalCase $case): JsonResponse
    {
        $this->authorize('delete', $case);

        $this->auditLog('deleted_case', $case);
        $case->delete();

        return response()->json(['message' => 'Case deleted successfully.']);
    }

    public function updateStatus(LegalCase $case, string $status): RedirectResponse
    {
        $this->authorize('update', $case);

        // FIX BUG (additional): Aligned allowed statuses with the Philippine workflow
        // defined in LegalCase model scopes. Old values ('open','ongoing','closed',
        // 'won','lost','dismissed') did not match the actual DB values.
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