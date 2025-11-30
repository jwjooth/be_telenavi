<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TodosExport;

class TodoController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Creating new todo', ['data' => $request->all()]);
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'due_date' => 'required|date|after_or_equal:today',
                'priority' => 'required|in:low,medium,high',
                'assignee' => 'nullable|string',
            ]);
            $todo = Todo::create($validated);

            Log::info('Todo created successfully', ['id' => $todo->id]);
            return ApiResponse::success($todo, 'Todo created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Error creating todo', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to create todo', 500);
        }
    }

    public function index(Request $request)
    {
        $query = Todo::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('assignee')) {
            $assignees = explode(',', $request->assignee);
            $query->whereIn('assignee', $assignees);
        }

        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('due_date', [$request->start, $request->end]);
        }

        if ($request->has('min') && $request->has('max')) {
            $query->whereBetween('time_tracked', [$request->min, $request->max]);
        }

        if ($request->has('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->has('priority')) {
            $priorities = explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        $todos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $todos,
            'total' => $todos->count()
        ]);
    }

    public function export(Request $request)
    {
        try {
            $query = Todo::query();

            // Filter: title
            if ($request->has('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            // Filter: assignee
            if ($request->has('assignee')) {
                $assignees = explode(',', $request->assignee);
                $query->whereIn('assignee', $assignees);
            }

            // Filter: due_date
            if ($request->has('start') && $request->has('end')) {
                $query->whereBetween('due_date', [$request->start, $request->end]);
            }

            // Filter: time_tracked
            if ($request->has('min') && $request->has('max')) {
                $query->whereBetween('time_tracked', [$request->min, $request->max]);
            }

            // Filter: status
            if ($request->has('status')) {
                $statuses = explode(',', $request->status);
                $query->whereIn('status', $statuses);
            }

            // Filter: priority
            if ($request->has('priority')) {
                $priorities = explode(',', $request->priority);
                $query->whereIn('priority', $priorities);
            }

            $todos = $query->get();

            return Excel::download(new TodosExport($todos), 'todos-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting todos: ' . $e->getMessage()
            ], 500);
        }
        exit;
    }
}
