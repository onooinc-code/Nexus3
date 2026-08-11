<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowSchedule;
use App\Services\WorkflowExecutor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkflowsHubController extends Controller
{
    /**
     * Main Dashboard View
     */
    public function index()
    {
        $workflows = Workflow::all();

        return view('WorkflowsHub.index', compact('workflows'));
    }

    /**
     * Execute a workflow manually
     */
    public function execute(Workflow $workflow, WorkflowExecutor $executor)
    {
        if ($workflow->isRunning()) {
            return response()->json([
                'code' => 'workflow_running',
                'message' => 'Workflow is already running',
            ], 409);
        }

        $result = $executor->execute($workflow, [], 'async', request()->user());

        $execution = WorkflowExecution::with('stepLogs')->find($result['execution_id']);

        return response()->json([
            'success' => true,
            'execution_id' => $execution->id,
            'status' => $execution->status,
            'message' => 'Workflow execution queued',
        ], 202);
    }

    /**
     * Show a specific execution payload
     */
    public function showExecution(WorkflowExecution $execution)
    {
        $execution->load(['workflow', 'stepLogs' => fn ($query) => $query->orderBy('created_at')]);

        return response()->json([
            'success' => true,
            'execution' => $execution,
        ]);
    }

    // --- New AJAX Endpoints for UI ---

    public function dashboardStats()
    {
        $totalWorkflows = Workflow::count();
        $activeSchedules = WorkflowSchedule::where('is_active', true)->count();
        $executionsToday = WorkflowExecution::whereDate('created_at', now()->today())->count();

        // Success Rate (last 24 hours)
        $executions24h = WorkflowExecution::where('created_at', '>=', now()->subDay());
        $total24h = $executions24h->count();
        $success24h = (clone $executions24h)->where('status', 'completed')->count();

        $successRate = $total24h > 0 ? round(($success24h / $total24h) * 100, 1) : 0;

        // Chart Data (Last 7 Days)
        $chartData = [
            'labels' => [],
            'success' => [],
            'failed' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = now()->subDays($i)->format('D');

            $dayExecutions = WorkflowExecution::whereDate('created_at', $date);
            $chartData['success'][] = (clone $dayExecutions)->where('status', 'completed')->count();
            $chartData['failed'][] = (clone $dayExecutions)->where('status', 'failed')->count();
        }

        return response()->json([
            'totalWorkflows' => $totalWorkflows,
            'activeSchedules' => $activeSchedules,
            'executionsToday' => number_format($executionsToday),
            'successRate' => $successRate,
            'chartData' => $chartData,
        ]);
    }

    public function executionsData()
    {
        // For simplicity, we just fetch the latest 100 executions.
        // In a true DataTables SSR, we'd handle pagination, search, and ordering parameters from request.
        $executions = WorkflowExecution::with('workflow')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($exec) {
                return [
                    'id' => '#EX-'.$exec->id,
                    'raw_id' => $exec->id,
                    'workflow_name' => $exec->workflow ? $exec->workflow->name : 'Unknown',
                    'status' => $exec->status,
                    'duration' => $exec->completed_at && $exec->started_at
                                    ? Carbon::parse($exec->completed_at)->diffInSeconds(Carbon::parse($exec->started_at)).'s'
                                    : 'N/A',
                    'trigger_source' => ucfirst($exec->trigger_source ?? 'Unknown'),
                    'started_at' => $exec->started_at ? Carbon::parse($exec->started_at)->diffForHumans() : 'Pending',
                ];
            });

        return response()->json([
            'data' => $executions,
        ]);
    }

    public function saveWorkflow(Request $request, $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'drawflow' => 'required|array',
        ]);

        // Drawflow exports a complex JSON. We store it in `steps` or `metadata->drawflow`.
        // The Nexus engine might expect a specific format in `steps`, so we'll store the raw Drawflow in `metadata`
        // and optionally map it to `steps` if needed, but for now we'll just save it to metadata so it can be reloaded.

        $metadata = $workflow->metadata ?? [];
        $metadata['drawflow'] = $validated['drawflow'];

        $workflow->metadata = $metadata;
        $workflow->save();

        return response()->json([
            'success' => true,
            'message' => 'Workflow layout saved successfully.',
        ]);
    }

    public function schedulesData()
    {
        $schedules = WorkflowSchedule::with('workflow')->get()->map(function ($sched) {
            return [
                'id' => $sched->id,
                'workflow_name' => $sched->workflow ? $sched->workflow->name : 'Unknown Workflow',
                'cron_expression' => $sched->cron_expression,
                'is_active' => $sched->is_active,
                'next_run_at' => $sched->next_run_at ? Carbon::parse($sched->next_run_at)->diffForHumans() : 'N/A',
            ];
        });

        // Also fetch active workflows to display their webhooks
        $webhooks = Workflow::where('is_active', true)->get()->map(function ($wf) {
            return [
                'id' => $wf->id,
                'name' => $wf->name,
                'url' => route('api.webhooks.workflows', ['workflow_id' => $wf->id]), // Assuming this route exists
            ];
        });

        return response()->json([
            'schedules' => $schedules,
            'webhooks' => $webhooks,
        ]);
    }

    public function toggleSchedule(Request $request, $id)
    {
        $schedule = WorkflowSchedule::findOrFail($id);
        $schedule->is_active = $request->boolean('is_active');
        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => 'Schedule status updated',
        ]);
    }
}
