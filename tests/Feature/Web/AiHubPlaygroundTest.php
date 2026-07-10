<?php

namespace Tests\Feature\Web;

use App\Jobs\AiHub\SimulateAiJob;
use App\Services\AiHubService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\TestCase;

class AiHubPlaygroundTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('ai_providers', function (Blueprint $table) {
            $table->string('id');
            $table->string('name');
            $table->boolean('is_active');
            $table->string('base_url')->nullable();
        });

        Schema::create('ai_models', function (Blueprint $table) {
            $table->string('id');
            $table->string('provider_id');
            $table->string('name');
            $table->string('api_identifier');
            $table->boolean('is_active');
        });

        DB::table('ai_providers')->insert([
            'id' => 'openai',
            'name' => 'OpenAI',
            'is_active' => true,
        ]);

        DB::table('ai_models')->insert([
            'id' => 'gpt-4',
            'provider_id' => 'openai',
            'name' => 'GPT-4',
            'api_identifier' => 'gpt-4',
            'is_active' => true,
        ]);

        DB::table('ai_providers')->insert([
            'id' => 'anthropic',
            'name' => 'Anthropic',
            'is_active' => true,
        ]);

        DB::table('ai_models')->insert([
            'id' => 'claude-3',
            'provider_id' => 'anthropic',
            'name' => 'Claude 3',
            'api_identifier' => 'claude-3',
            'is_active' => true,
        ]);
    }

    public function test_it_simulates_chat_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('simulateChat')
                ->once()
                ->with('openai', 'gpt-4', 'Hello there')
                ->andReturn('Mocked AI response');
        });

        $response = $this->postJson(route('hub.models.playground.chat'), [
            'provider_id' => 'openai',
            'model_id' => 'gpt-4',
            'message' => 'Hello there',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Chat simulated successfully.',
                'data' => [
                    'response' => 'Mocked AI response',
                ],
            ]);
    }

    public function test_simulate_chat_fails_validation_without_provider_id(): void
    {
        $response = $this->postJson(route('hub.models.playground.chat'), [
            'model_id' => 'gpt-4',
            'message' => 'Hello',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider_id']);
    }

    public function test_simulate_chat_fails_validation_with_invalid_provider_id(): void
    {
        $response = $this->postJson(route('hub.models.playground.chat'), [
            'provider_id' => 'invalid-provider',
            'model_id' => 'gpt-4',
            'message' => 'Hello',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider_id']);
    }

    public function test_simulate_chat_fails_validation_with_mismatched_model_id(): void
    {
        // gpt-4 belongs to openai, so it should fail when provider is anthropic
        $response = $this->postJson(route('hub.models.playground.chat'), [
            'provider_id' => 'anthropic',
            'model_id' => 'gpt-4',
            'message' => 'Hello',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['model_id']);
    }

    public function test_it_dispatches_ai_job_successfully(): void
    {
        Queue::fake();

        $response = $this->postJson(route('hub.models.playground.dispatch-job'), [
            'provider_id' => 'anthropic',
            'model_id' => 'claude-3',
            'message' => 'Run a job',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'AI job dispatched to Horizon successfully.',
            ]);

        Queue::assertPushed(SimulateAiJob::class, function ($job) {
            return $job->providerId === 'anthropic'
                && $job->modelId === 'claude-3'
                && $job->message === 'Run a job';
        });
    }

    public function test_dispatch_job_fails_validation_without_message(): void
    {
        $response = $this->postJson(route('hub.models.playground.dispatch-job'), [
            'provider_id' => 'openai',
            'model_id' => 'gpt-4',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_dispatch_job_fails_validation_with_mismatched_model_id(): void
    {
        $response = $this->postJson(route('hub.models.playground.dispatch-job'), [
            'provider_id' => 'openai',
            'model_id' => 'claude-3',
            'message' => 'Hello',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['model_id']);
    }
}
