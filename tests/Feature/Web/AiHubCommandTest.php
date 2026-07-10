<?php

namespace Tests\Feature\Web;

use App\Services\AiHubService;
use Mockery\MockInterface;
use Tests\TestCase;

class AiHubCommandTest extends TestCase
{
    public function test_sync_models_command_runs_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('syncModels')
                ->once()
                ->with(null)
                ->andReturn(5);
        });

        $this->artisan('ai-hub:sync-models')
            ->expectsOutput('Starting AI Models Synchronization...')
            ->expectsOutput('Successfully synced 5 models.')
            ->assertExitCode(0);
    }

    public function test_sync_models_command_handles_provider_option(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('syncModels')
                ->once()
                ->with('openai')
                ->andReturn(2);
        });

        $this->artisan('ai-hub:sync-models', ['--provider' => 'openai'])
            ->expectsOutput('Starting AI Models Synchronization...')
            ->expectsOutput('Successfully synced 2 models.')
            ->assertExitCode(0);
    }

    public function test_sync_models_command_handles_failure(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('syncModels')
                ->once()
                ->andThrow(new \Exception('Connection timeout'));
        });

        $this->artisan('ai-hub:sync-models')
            ->expectsOutput('Starting AI Models Synchronization...')
            ->expectsOutput('Failed to sync models: Connection timeout')
            ->assertExitCode(1);
    }

    public function test_rotate_keys_command_runs_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('rotateKeys')
                ->once()
                ->with(false)
                ->andReturn(3);
        });

        $this->artisan('ai-hub:rotate-keys')
            ->expectsOutput('Starting AI API Key Rotation...')
            ->expectsOutput('Successfully rotated 3 keys.')
            ->assertExitCode(0);
    }

    public function test_rotate_keys_command_handles_force_option(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('rotateKeys')
                ->once()
                ->with(true)
                ->andReturn(10);
        });

        $this->artisan('ai-hub:rotate-keys', ['--force' => true])
            ->expectsOutput('Starting AI API Key Rotation...')
            ->expectsOutput('Successfully rotated 10 keys.')
            ->assertExitCode(0);
    }
}
