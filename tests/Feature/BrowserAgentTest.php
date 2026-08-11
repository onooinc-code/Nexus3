<?php

namespace Tests\Feature;

use App\Events\DOMEventDispatched;
use App\Events\TaskDispatchedToBrowserAgent;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\User;
use Database\Seeders\ErtugrulBrowserAgentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BrowserAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_agent_can_be_seeded_and_fetched()
    {
        $this->seed(ErtugrulBrowserAgentSeeder::class);

        $agent = Agent::where('key', 'ertugrul_browser_agent')->first();

        $this->assertNotNull($agent);
        $this->assertEquals('Ertugrul Browser Orchestrator', $agent->name);
        $this->assertEquals('specialized', $agent->type);
    }

    public function test_pending_browser_tasks_endpoint_returns_tasks()
    {
        $user = User::factory()->create(['is_admin' => true]);

        AgentTask::create([
            'title' => 'Test FB Messenger Response',
            'type' => 'agent',
            'status' => 'todo',
            'target_agent_id' => 'ertugrul_browser_agent',
            'origin_agent_id' => 'openclaw_core',
            'task_type' => 'event_driven',
            'dynamic_system_instruction' => 'Click input box and send automated reply.',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/agent-tasks/pending?target_agent_id=ertugrul_browser_agent');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.title', 'Test FB Messenger Response');
    }

    public function test_dom_event_trigger_endpoint_broadcasts_and_creates_task()
    {
        Event::fake([DOMEventDispatched::class, TaskDispatchedToBrowserAgent::class]);

        $user = User::factory()->create(['is_admin' => true]);

        $payload = [
            'event_name' => 'fb_messenger_new_message',
            'dom_selector' => 'div[aria-label="Messages"]',
            'page_url' => 'https://facebook.com/messages/t/12345',
            'auto_task' => true,
            'dynamic_system_instruction' => 'Parse incoming message and draft response',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/events/dom-trigger', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        Event::assertDispatched(DOMEventDispatched::class);
        Event::assertDispatched(TaskDispatchedToBrowserAgent::class);

        $this->assertDatabaseHas('agent_tasks', [
            'target_agent_id' => 'ertugrul_browser_agent',
            'task_type' => 'event_driven',
        ]);
    }

    public function test_task_status_update_with_proof()
    {
        $user = User::factory()->create(['is_admin' => true]);

        $task = AgentTask::create([
            'title' => 'Browser Task with Proof',
            'type' => 'agent',
            'status' => 'todo',
            'target_agent_id' => 'ertugrul_browser_agent',
        ]);

        $proof = [
            'screenshot_url' => 'https://nexus.local/storage/proofs/screenshot_123.jpg',
            'logs' => ['Clicked button', 'Entered text', 'Form submitted'],
            'dom_state' => 'success_banner_visible',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/agent-tasks/{$task->id}/status", [
                'status' => 'completed',
                'execution_proof' => $proof,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.execution_proof.screenshot_url', 'https://nexus.local/storage/proofs/screenshot_123.jpg');
    }
}
