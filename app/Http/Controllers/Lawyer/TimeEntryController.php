<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeEntryRequest;
use App\Models\LegalCase;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;

class TimeEntryController extends Controller
{
    public function index(LegalCase $case): JsonResponse
    {
        $this->authorize('view', $case);

        $entries = TimeEntry::where('case_id', $case->id)
            ->where('lawyer_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        $totalHours    = $entries->sum('hours');
        $totalBillable = $entries->sum('total');

        return response()->json([
            'entries'        => $entries,
            'total_hours'    => $totalHours,
            'total_billable' => $totalBillable,
        ]);
    }

    public function store(StoreTimeEntryRequest $request): JsonResponse
    {
        $case = LegalCase::findOrFail($request->case_id);
        $this->authorize('update', $case);

        // Get lawyer's current billing rate
        $rate = auth()->user()->billingRates()
            ->where('effective_date', '<=', $request->date)
            ->orderBy('effective_date', 'desc')
            ->first();

        $hourlyRate = $request->hourly_rate ?? ($rate?->hourly_rate ?? 0);

        $entry = TimeEntry::create([
            'case_id'     => $request->case_id,
            'lawyer_id'   => auth()->id(),
            'date'        => $request->date,
            'hours'       => $request->hours,
            'hourly_rate' => $hourlyRate,
            'description' => $request->description,
            'is_billed'   => false,
        ]);

        return response()->json([
            'message' => 'Time entry recorded.',
            'entry'   => $entry,
        ], 201);
    }

    public function destroy(TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('update', $timeEntry->case);

        if ($timeEntry->is_billed) {
            return response()->json(['message' => 'Cannot delete a billed time entry.'], 422);
        }

        $timeEntry->delete();

        return response()->json(['message' => 'Time entry deleted.']);
    }
}