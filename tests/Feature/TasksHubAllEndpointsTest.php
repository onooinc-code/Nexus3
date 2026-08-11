<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasksHubAllEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_tasks_stats_endpoints()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/stats');
        $response->assertStatus(200)->assertJsonStructure(['data' => ['total', 'pending', 'running', 'completed', 'failed']]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/active');
        $response->assertStatus(200)->assertJsonStructure(['data']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/queue-stats');
        $response->assertStatus(200)->assertJsonStructure(['data']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/routing-stats');
        $response->assertStatus(200)->assertJsonStructure(['data']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/stats/by-type');
        $response->assertStatus(200)->assertJsonStructure(['data']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/stats/timeline');
        $response->assertStatus(200)->assertJsonStructure(['data' => ['labels', 'completed', 'failed']]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/stats/agents');
        $response->assertStatus(200)->assertJsonStructure(['data' => ['labels', 'data']]);
    }

    public function test_task_creation_by_type_and_fetching()
    {
        // Manual
        $res = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/tasks/manual', [
                'title' => 'Manual Task Test',
                'description' => 'Manual Task Description',
            ]);
        $res->assertStatus(201);

        // System
        $res = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/tasks/system', [
                'title' => 'System Task Test',
                'description' => 'System Task Description',
            ]);
        $res->assertStatus(201);

        // Get by type
        $res = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks/type/manual');
        $res->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_task_crud_and_actions()
    {
        // Index
        $res = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/tasks');
        $res->assertStatus(200);

        // Store
        $res = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/tasks', [
            'title' => 'Task via Store',
            'type' => 'system',
            'priority' => 5,
        ]);
        $res->assertStatus(201);
        $taskId = $res->json('data.id');

        // Show
        $res = $this->actingAs($this->user, 'sanctum')->getJson("/api/v1/tasks/{$taskId}");
        $res->assertStatus(200);

        // Update
        $res = $this->actingAs($this->user, 'sanctum')->patchJson("/api/v1/tasks/{$taskId}", [
            'title' => 'Updated Title',
        ]);
        $res->assertStatus(200);

        // Status update to todo so execute works
        $res = $this->actingAs($this->user, 'sanctum')->patchJson("/api/v1/tasks/{$taskId}/status", [
            'status' => 'todo',
        ]);
        $res->assertStatus(200);

        // Execute / Pause / Resume / Logs / Cancel
        $this->actingAs($this->user, 'sanctum')->postJson("/api/v1/tasks/{$taskId}/execute")->assertStatus(200);
        $this->actingAs($this->user, 'sanctum')->postJson("/api/v1/tasks/{$taskId}/pause")->assertStatus(200);
        $this->actingAs($this->user, 'sanctum')->postJson("/api/v1/tasks/{$taskId}/resume")->assertStatus(200);
        $this->actingAs($this->user, 'sanctum')->getJson("/api/v1/tasks/{$taskId}/logs")->assertStatus(200);
        $this->actingAs($this->user, 'sanctum')->postJson("/api/v1/tasks/{$taskId}/cancel")->assertStatus(200);

        // Destroy
        $res = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/tasks/{$taskId}");
        $res->assertStatus(200);
    }

    public function test_task_template_endpoints()
    {
        // Store
        $res = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/task-templates', [
            'name' => 'Data Cleanup Template',
            'task_type' => 'manual',
            'title_template' => 'Cleanup {target}',
            'expected_variables' => ['target'],
        ]);
        $res->assertStatus(201);
        $templateId = $res->json('data.id');

        // Index
        $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/task-templates')->assertStatus(200);

        // Show
        $this->actingAs($this->user, 'sanctum')->getJson("/api/v1/task-templates/{$templateId}")->assertStatus(200);

        // Update
        $this->actingAs($this->user, 'sanctum')->putJson("/api/v1/task-templates/{$templateId}", [
            'name' => 'Updated Data Cleanup Template',
            'task_type' => 'manual',
            'title_template' => 'Cleanup {target}',
        ])->assertStatus(200);

        // Spawn
        $this->actingAs($this->user, 'sanctum')->postJson("/api/v1/task-templates/{$templateId}/spawn", [
            'variables' => ['target' => 'logs'],
        ])->assertStatus(201);

        // Destroy
        $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/task-templates/{$templateId}")->assertStatus(200);
    }
}
