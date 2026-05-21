<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourtType::all());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|unique:court_types,name',
            'jurisdiction' => 'nullable|string',
        ]);

        $courtType = CourtType::create($data);

        return response()->json([
            'message'    => 'Court type created.',
            'court_type' => $courtType,
        ], 201);
    }

    public function update(Request $request, CourtType $courtType): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|unique:court_types,name,' . $courtType->id,
            'jurisdiction' => 'nullable|string',
            'is_active'    => 'sometimes|boolean',
        ]);

        $courtType->update($data);

        return response()->json([
            'message'    => 'Court type updated.',
            'court_type' => $courtType->fresh(),
        ]);
    }

    public function destroy(CourtType $courtType): JsonResponse
    {
        if ($courtType->cases()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete court type with existing cases.',
            ], 422);
        }

        $courtType->delete();

        return response()->json(['message' => 'Court type deleted.']);
    }
}