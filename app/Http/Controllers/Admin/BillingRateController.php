<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingRate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingRateController extends Controller
{
    public function index(): JsonResponse
    {
        $rates = BillingRate::with('lawyer:id,name,email')
            ->orderBy('effective_date', 'desc')
            ->get();

        return response()->json($rates);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lawyer_id'      => 'required|exists:users,id',
            'hourly_rate'    => 'required|numeric|min:0',
            'currency'       => 'sometimes|string|max:10',
            'effective_date' => 'required|date',
        ]);

        // Verify the user is actually a lawyer
        $lawyer = User::findOrFail($data['lawyer_id']);
        if (!$lawyer->isLawyer()) {
            return response()->json(['message' => 'User is not a lawyer.'], 422);
        }

        $rate = BillingRate::create($data);

        return response()->json([
            'message' => 'Billing rate set.',
            'rate'    => $rate,
        ], 201);
    }

    public function destroy(BillingRate $billingRate): JsonResponse
    {
        $billingRate->delete();
        return response()->json(['message' => 'Billing rate deleted.']);
    }
}