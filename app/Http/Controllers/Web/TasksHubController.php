<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AgentTask;
use Illuminate\Http\Request;

class TasksHubController extends Controller
{
    public function index()
    {
        $tasks = AgentTask::with(['agent', 'workflow'])->orderBy('created_at', 'desc')->get();
        return view('TasksHub.index', compact('tasks'));
    }

    public function show(AgentTask $task)
    {
        $task->load(['agent', 'workflow']);
        return view('TasksHub.show', compact('task'));
    }
}
