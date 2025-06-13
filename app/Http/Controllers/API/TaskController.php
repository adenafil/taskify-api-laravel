<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskCollection;
use App\Models\Task;
use App\Models\User;
use App\Exports\TasksExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:255',
            'categoryIcon' => 'nullable|string|max:255',
            'due_date' => 'required|date',
            'priority' => 'nullable|string|in:low,medium,high',
            'status' => 'nullable|string|in:in_progress,completed,expired',
        ]);

        $user = $request->user();

        $task = new Task();
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->category = $request->input('category', 'Home');
        $task->categoryIcon = $request->input('categoryIcon', 'i-heroicons-home');
        $task->due_date = $request->input('due_date');
        $task->priority = $request->input('priority', 'medium'); // Default to 'priority' if not provided dawg
        $task->status = $request->input('status', 'in_progress'); // Default to 'in progress' if not provided dawg
        $task->user_id = $user->id; // Set the user_id to the authenticated user's ID
        $task->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully',
            'task' => $task
        ], 201);
    }

    public function get(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $status = $request->input('status', 'all'); // Default to 'all'
        $priority = $request->input('priority'); // No default, optional filter

        $query = Task::where('user_id', $user->id);

        // Apply status filter
        if ($status !== 'all') {
            // For in_progress or completed status
            $query->where('status', $status);
        }

        // Apply priority filter if provided
        if ($priority && in_array($priority, ['low', 'medium', 'high'])) {
            $query->where('priority', $priority);
        }

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('priority', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('due_date', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return new TaskCollection($tasks);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'nullable|string|max:255',
            'categoryIcon' => 'nullable|string|max:255',
            'due_date' => 'sometimes|required|date',
            'priority' => 'sometimes|nullable|string|in:low,medium,high',
            'status' => 'sometimes|nullable|string|in:in_progress,completed',
        ]);

        $user = $request->user();
        $task = Task::where('id', $id)->where('user_id', $user->id)->first();

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        // Only update fields that are present in the request
        $updatableFields = ['title', 'description', 'category', 'categoryIcon', 'due_date', 'priority', 'status'];
        $updateData = [];


        foreach ($updatableFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        $task->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'task' => $task
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $task = Task::where('id', $id)->where('user_id', $user->id)->first();

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully'
        ]);
    }

    public function getCategoryUser(Request $request)
    {
        $user = $request->user();
        $categories = Task::select(['category', 'categoryIcon'])->where('user_id', $user->id)->groupBy('category')
            ->get();


        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function exportToExcel(Request $request)
    {
        // Jika tidak ada userId di parameter, gunakan user yang sedang login
        $targetUserId = $request->user()->id;

        // Cek apakah user exists
        $user = User::find($targetUserId);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Cek apakah user memiliki tasks
        $taskCount = Task::where('user_id', $targetUserId)->count();
        if ($taskCount === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tasks found for this user'
            ], 404);
        }

        // Generate filename
        $filename = 'tasks_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // Export dan download
        return Excel::download(new TasksExport($targetUserId), $filename);
    }

}
