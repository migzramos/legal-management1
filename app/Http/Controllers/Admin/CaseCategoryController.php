<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CaseCategory::all());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:case_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = CaseCategory::create($data);

        return response()->json([
            'message'  => 'Category created.',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, CaseCategory $caseCategory): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|unique:case_categories,name,' . $caseCategory->id,
            'description' => 'nullable|string',
            'is_active'   => 'sometimes|boolean',
        ]);

        $caseCategory->update($data);

        return response()->json([
            'message'  => 'Category updated.',
            'category' => $caseCategory->fresh(),
        ]);
    }

    public function destroy(CaseCategory $caseCategory): JsonResponse
    {
        if ($caseCategory->cases()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing cases.',
            ], 422);
        }

        $caseCategory->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}