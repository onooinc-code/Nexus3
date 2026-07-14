<?php

namespace Tests\Feature;

use App\Jobs\ExecuteWorkflowStepJob;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Services\TaskRoutingService;
use App\Services\Workflows\WorkflowInterpreter;
use App\Services\Workflows\WorkflowTaskDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WorkflowTasksOverhaulTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_routing_works_as_expected()
    {
        // Create the agents in the database first
        Agent::factory()->create(['type' => Agent::TYPE_REFLECTION]);
        Agent::factory()->create(['type' => Agent::TYPE_SPECIALIZED]);
        Agent::factory()->create(['type' => Agent::TYPE_SUPERVISOR]);
        Agent::factory()->create(['type' => Agent::TYPE_AUTONOMOUS]);

        $routingService = app(TaskRoutingService::class);

        // 1. Create a task that looks like a reflection task
        $task = new AgentTask([
            'title' => 'Analyze the system performance',
            'description' => 'Please evaluate and review the CPU cycles.',
        ]);
        $routes = $routingService->route($task);
        $this->assertEquals(Agent::TYPE_REFLECTION, $routes['agent_type']);

        // 2. Create a research task
        $task2 = new AgentTask([
            'title' => 'Research competitors',
            'description' => 'Scrape the public websites and fetch prices.',
        ]);
        $routes2 = $routingService->route($task2);
        $this->assertEquals(Agent::TYPE_SPECIALIZED, $routes2['agent_type']);

        // 3. Create a coordination task
        $task3 = new AgentTask([
            'title' => 'Coordinate workflow execution',
            'description' => 'Manage conflicts and oversee the teams.',
        ]);
        $routes3 = $routingService->route($task3);
        $this->assertEquals(Agent::TYPE_SUPERVISOR, $routes3['agent_type']);

        // 4. Default task
        $task4 = new AgentTask([
            'title' => 'Write a short greeting email',
            'description' => 'Say hello to the customer.',
        ]);
        $routes4 = $routingService->route($task4);
        $this->assertEquals(Agent::TYPE_AUTONOMOUS, $routes4['agent_type']);
    }

    public function test_http_request_step_type_executes_successfully()
    {
        Http::fake([
            'https://api.github.com/users/*' => Http::response(['login' => 'octocat'], 200, ['Headers']),
        ]);

        $dispatcher = app(WorkflowTaskDispatcher::class);
        $execution = new WorkflowExecution([
            'id' => 'test-exec-id',
            'workflow_id' => 1,
        ]);

        $step = [
            'id' => 'step-github-api',
            'name' => 'Call GitHub API',
            'type' => 'http_request',
            'input' => [
                'url' => 'https://api.github.com/users/octocat',
                'method' => 'GET',
                'timeout' => 5,
            ],
        ];

        $result = $dispatcher->dispatch($execution, $step, []);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['output']['status']);
        $this->assertEquals('octocat', $result['output']['body']['login']);
    }

    public function test_parallel_branches_barrier_join_logic()
    {
        $interpreter = app(WorkflowInterpreter::class);
        $dispatcher = app(WorkflowTaskDispatcher::class);

        $workflow = Workflow::create([
            'name' => 'Parallel Workflow Test',
            'key' => 'parallel_workflow_test',
            'is_active' => true,
            'nodes' => [],
            'edges' => [],
            'steps' => [
                [
                    'id' => 'step-parallel',
                    'name' => 'Parallel Step',
                    'type' => 'parallel',
                    'branches' => [
                        [
                            'id' => 'branch-1',
                            'name' => 'Branch One',
                            'type' => 'log',
                            'message' => 'Branch 1 executed',
                        ],
                        [
                            'id' => 'branch-2',
                            'name' => 'Branch Two',
                            'type' => 'log',
                            'message' => 'Branch 2 executed',
                        ],
                    ],
                ],
                [
                    'id' => 'step-final',
                    'name' => 'Final Step',
                    'type' => 'log',
                    'message' => 'All done',
                ],
            ],
        ]);

        $version = $workflow->versions()->create([
            'version_number' => 1,
            'definition' => ['steps' => $workflow->steps],
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // Start execution
        $execution = WorkflowExecution::create([
            'id' => 'exec-parallel-test',
            'workflow_id' => $workflow->id,
            'workflow_version_id' => $version->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'input_payload' => [],
            'runtime_state' => [
                'current_step_index' => 0,
                'variables' => [],
                'depth' => 0,
            ],
        ]);

        // Run execution (should pause at step-parallel waiting for branches)
        $execution = $interpreter->run($execution);

        $this->assertEquals(WorkflowExecution::STATUS_PAUSED, $execution->status);
        $this->assertEquals('parallel_branches', $execution->runtime_state['waiting_for']['type']);
        $this->assertEquals(2, $execution->runtime_state['waiting_for']['total_branches']);
        $this->assertCount(0, $execution->runtime_state['waiting_for']['completed_branches']);

        // Execute branch-1 step job
        $branch1Step = $workflow->steps[0]['branches'][0];
        $job1 = new ExecuteWorkflowStepJob($execution->id, $branch1Step, []);
        $job1->handle($dispatcher);

        $execution->refresh();
        $this->assertEquals(WorkflowExecution::STATUS_PAUSED, $execution->status);
        $this->assertCount(1, $execution->runtime_state['waiting_for']['completed_branches']);

        // Execute branch-2 step job (should trigger resume and finish workflow)
        $branch2Step = $workflow->steps[0]['branches'][1];
        $job2 = new ExecuteWorkflowStepJob($execution->id, $branch2Step, []);
        $job2->handle($dispatcher);

        $execution->refresh();
        // Since we are running in sync testing without full async queue runner,
        // the second job dispatches ExecuteWorkflowJob asynchronously which runs immediately.
        // Let's verify it successfully ran and completed the workflow execution:
        $this->assertEquals(WorkflowExecution::STATUS_COMPLETED, $execution->status);
        $this->assertNull($execution->runtime_state['waiting_for']);
    }
}
