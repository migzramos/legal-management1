<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Models\LegalCase;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(LegalCase $case): JsonResponse
    {
        $this->authorize('view', $case);

        $tasks = Task::where('case_id', $case->id)
            ->with('assignedTo:id,name,role')
            ->latest()
            ->get();

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Task created successfully.',
            'task'    => $task->load('assignedTo:id,name'),
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task->case);

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'sometimes|in:low,medium,high,urgent',
            'status'      => 'sometimes|in:pending,in_progress,completed,cancelled',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        if (isset($data['status']) && $data['status'] === 'completed') {
            $data['completed_at'] = now();
        }

        $task->update($data);

        return response()->json([
            'message' => 'Task updated successfully.',
            'task'    => $task->fresh(),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('update', $task->case);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }
}