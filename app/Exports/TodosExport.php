<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class TodosExport implements FromArray
{
    protected $todos;

    public function __construct($todos)
    {
        $this->todos = $todos;
    }

    public function array(): array
    {
        $rows = [];

        // Header row
        $rows[] = ['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority'];

        // Data rows
        foreach ($this->todos as $todo) {
            $rows[] = [
                $todo->title,
                $todo->assignee,
                $todo->due_date,
                $todo->time_tracked,
                $todo->status,
                $todo->priority,
            ];
        }

        // Summary row
        $totalTodos = count($this->todos);
        $totalTime = collect($this->todos)->sum('time_tracked');
        
        $rows[] = []; // Empty row
        $rows[] = ['TOTAL TODOS: ' . $totalTodos, '', '', $totalTime, '', ''];

        return $rows;
    }
}