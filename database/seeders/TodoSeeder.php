<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/TodoSeeder.php
    public function run()
    {
        Todo::create([
            'title' => 'Complete Project',
            'due_date' => '2025-12-31',
            'priority' => 'high',
            'assignee' => 'Jordan'
        ]);
    }
}
