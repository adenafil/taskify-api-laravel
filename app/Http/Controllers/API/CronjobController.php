<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class CronjobController extends Controller
{
    public function updateExpiredTask()
    {
        $tasks = Task::where('status', 'in_progress')
            ->where('due_date', '<', now())
            ->get();

        foreach ($tasks as $task) {
            $task->update(['status' => 'expired']);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Expired tasks updated successfully',
        ]);
    }
}
