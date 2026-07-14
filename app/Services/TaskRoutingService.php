<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\Workflow;
use Illuminate\Support\Facades\Log;

class TaskRoutingService
{
    protected AgentRegistry $registry;

    protected array $routes = [];

    public function __construct(AgentRegistry $registry)
    {
        $this->registry = $registry;
        $this->registerDefaultRoutes();
    }

    public function route(AgentTask $task): array
    {
        $workflow = $task->workflow;
        $agentType = $task->metadata['agent_type'] ?? null;

        // 1. Try to route based on Workflow step configuration
        if ($workflow && $workflow->steps) {
            $step = $this->findStepForTask($workflow, $task);
            if ($step) {
                $agentType = $step['agent_type'] ?? $agentType;
            }
        }

        // 2. If no agent type is set, use keyword analysis (Smart Routing)
        if (! $agentType) {
            $agentType = $this->analyzeTaskKeywords($task);
            Log::info("Task routed via keyword analysis to: {$agentType}", [
                'task_id' => $task->id,
            ]);
        }

        // 3. Resolve the agent and its handler
        if ($agentType && $this->registry->has($agentType)) {
            $agent = Agent::where('type', $agentType)->first();
            if ($agent) {
                Log::info("Task routed to agent type: {$agentType}", [
                    'task_id' => $task->id,
                    'agent_id' => $agent->id,
                ]);

                return [
                    'type' => 'agent',
                    'agent' => $agent,
                    'agent_type' => $agentType,
                    'handler' => $this->registry->resolve($agent),
                ];
            }
        }

        Log::warning('No suitable agent found for task', [
            'task_id' => $task->id,
            'agent_type' => $agentType,
        ]);

        return [
            'type' => 'default',
            'agent' => null,
            'agent_type' => null,
            'handler' => null,
        ];
    }

    /**
     * Analyze task title and description to determine the best agent type.
     */
    protected function analyzeTaskKeywords(AgentTask $task): string
    {
        $text = strtolower(($task->title ?? '').' '.($task->description ?? ''));

        // Check for reflection/analysis tasks
        if (preg_match('/\b(analyze|evaluation|evaluate|review|reflection|audit|assess)\b/', $text)) {
            return Agent::TYPE_REFLECTION;
        }

        // Check for specialized/research/tool tasks
        if (preg_match('/\b(research|search|scrape|fetch|query|tool|specialized)\b/', $text)) {
            return Agent::TYPE_SPECIALIZED;
        }

        // Check for supervision/coordination tasks
        if (preg_match('/\b(supervise|coordinate|approve|manage|conflict|oversee)\b/', $text)) {
            return Agent::TYPE_SUPERVISOR;
        }

        // Check for complex/team tasks
        if (preg_match('/\b(team|group|collaborate|complex|parallel|workflow)\b/', $text)) {
            return Agent::TYPE_TEAM;
        }

        // Default to autonomous agent for standard tasks
        return Agent::TYPE_AUTONOMOUS;
    }

    protected function findStepForTask(Workflow $workflow, AgentTask $task): ?array
    {
        $steps = $workflow->steps ?? [];
        $taskTitle = strtolower($task->title ?? '');

        foreach ($steps as $step) {
            $stepName = strtolower($step['name'] ?? $step['title'] ?? '');
            if (str_contains($taskTitle, $stepName) || str_contains($stepName, $taskTitle)) {
                return $step;
            }
        }

        return $steps[0] ?? null;
    }

    protected function registerDefaultRoutes(): void
    {
        $this->routes = [
            'simple' => [
                'agent_type' => Agent::TYPE_AUTONOMOUS,
                'description' => 'Route simple tasks to autonomous agents',
            ],
            'complex' => [
                'agent_type' => Agent::TYPE_TEAM,
                'description' => 'Route complex tasks to team agents',
            ],
            'analysis' => [
                'agent_type' => Agent::TYPE_REFLECTION,
                'description' => 'Route analysis tasks to reflection agents',
            ],
            'research' => [
                'agent_type' => Agent::TYPE_SPECIALIZED,
                'description' => 'Route research tasks to specialized agents',
            ],
            'coordination' => [
                'agent_type' => Agent::TYPE_SUPERVISOR,
                'description' => 'Route coordination tasks to supervisor agents',
            ],
        ];
    }

    public function getRoute(string $taskType): ?array
    {
        return $this->routes[$taskType] ?? null;
    }

    public function getAllRoutes(): array
    {
        return $this->routes;
    }

    public function registerRoute(string $name, array $config): void
    {
        $this->routes[$name] = $config;
        Log::info("Route registered: {$name}");
    }

    public function getStats(): array
    {
        return [
            'registered_routes' => count($this->routes),
            'route_names' => array_keys($this->routes),
            'registered_agent_types' => $this->registry->getRegisteredTypes(),
        ];
    }
}
