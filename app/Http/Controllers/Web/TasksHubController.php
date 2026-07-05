<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTask;

class TasksHubController extends Controller
{
    public function index()
    {
        $tasks = AgentTask::with(['agent', 'workflow'])->orderBy('created_at', 'desc')->get();
        $agents = Agent::where('is_active', true)->get();

        return view('TasksHub.index', compact('tasks', 'agents'));
    }

    public function show(AgentTask $task)
    {
        $task->load(['agent', 'workflow']);
        $agents = Agent::where('is_active', true)->get();

        return view('TasksHub.show', compact('task', 'agents'));
    }
}
