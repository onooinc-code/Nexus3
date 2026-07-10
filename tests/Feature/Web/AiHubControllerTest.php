<?php

namespace Tests\Feature\Web;

use App\Services\AiHubService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\TestCase;

class AiHubControllerTest extends TestCase
{
    public function test_it_toggles_provider_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('toggleProvider')
                ->once()
                ->with('openai', true)
                ->andReturn(['provider' => 'openai', 'is_active' => true]);
        });

        $response = $this->postJson(route('hub.models.providers.toggle'), [
            'provider' => 'openai',
            'is_active' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Provider toggled successfully.',
                'data' => [
                    'provider' => 'openai',
                    'is_active' => true,
                ],
            ]);
    }

    public function test_toggle_provider_fails_validation_without_provider(): void
    {
        $response = $this->postJson(route('hub.models.providers.toggle'), [
            'is_active' => true,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider']);
    }

    public function test_toggle_provider_fails_validation_without_is_active(): void
    {
        $response = $this->postJson(route('hub.models.providers.toggle'), [
            'provider' => 'openai',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_active']);
    }

    public function test_it_stores_api_key_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('storeApiKey')
                ->once()
                ->with('openai', 'sk-12345', 'Default Key')
                ->andReturn(['provider' => 'openai', 'stored' => true]);
        });

        $response = $this->postJson(route('hub.models.api-keys.store'), [
            'provider' => 'openai',
            'api_key' => 'sk-12345',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'API key stored successfully.',
                'data' => [
                    'provider' => 'openai',
                    'stored' => true,
                ],
            ]);
    }

    public function test_store_api_key_fails_validation_without_provider(): void
    {
        $response = $this->postJson(route('hub.models.api-keys.store'), [
            'api_key' => 'sk-12345',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider']);
    }

    public function test_store_api_key_fails_validation_without_api_key(): void
    {
        $response = $this->postJson(route('hub.models.api-keys.store'), [
            'provider' => 'openai',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['api_key']);
    }

    public function test_it_stores_provider_successfully(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->string('id');
        });

        $payload = [
            'id' => 'test-new-provider-999999999',
            'name' => 'Test Provider',
            'base_url' => 'https://1.1.1.1',
            'is_active' => true,
        ];

        $this->mock(AiHubService::class, function (MockInterface $mock) use ($payload) {
            $mock->shouldReceive('storeProvider')
                ->once()
                ->with($payload)
                ->andReturn(array_merge($payload, ['created' => true]));
        });

        $response = $this->postJson(route('hub.models.providers.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Provider added successfully.',
                'data' => [
                    'id' => 'test-new-provider-999999999',
                    'created' => true,
                ],
            ]);
    }

    public function test_store_provider_fails_validation_without_id(): void
    {
        $response = $this->postJson(route('hub.models.providers.store'), [
            'name' => 'Test',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id']);
    }

    public function test_it_toggles_model_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('toggleModel')
                ->once()
                ->with('gpt-4', true);
        });

        $response = $this->postJson(route('hub.models.toggle', ['id' => 'gpt-4']), [
            'is_active' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_toggle_model_fails_validation_without_is_active(): void
    {
        $response = $this->postJson(route('hub.models.toggle', ['id' => 'gpt-4']), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_active']);
    }

    public function test_it_revokes_api_key_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('revokeApiKey')
                ->once()
                ->with('key-123');
        });

        $response = $this->deleteJson(route('hub.models.api-keys.revoke', ['id' => 'key-123']));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'API key revoked successfully.',
            ]);
    }

    public function test_it_gets_api_keys_stats(): void
    {
        $stats = [
            'total' => 5,
            'active' => 3,
            'providers' => ['openai' => 2, 'anthropic' => 3],
        ];

        $this->mock(AiHubService::class, function (MockInterface $mock) use ($stats) {
            $mock->shouldReceive('getApiKeysStats')
                ->once()
                ->andReturn($stats);
        });

        $response = $this->getJson(route('hub.models.api-keys.stats'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => $stats,
            ]);
    }

    public function test_it_updates_budget_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateBudget')
                ->once()
                ->with('monthly', 500.0)
                ->andReturn(['type' => 'monthly', 'limit' => 500.0]);
        });

        $response = $this->postJson(route('hub.models.budget.update'), [
            'type' => 'monthly',
            'limit' => 500,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Budget updated successfully.',
                'data' => [
                    'type' => 'monthly',
                    'limit' => 500,
                ],
            ]);
    }

    public function test_it_gets_cost_charts_successfully(): void
    {
        $chartsData = [
            'dates' => ['2026-07-01', '2026-07-02'],
            'series' => [
                'OpenAI' => [1.5, 2.0],
            ],
        ];

        $this->mock(AiHubService::class, function (MockInterface $mock) use ($chartsData) {
            $mock->shouldReceive('getCostCharts')
                ->once()
                ->andReturn($chartsData);
        });

        $response = $this->getJson(route('hub.models.cost-charts'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => $chartsData,
            ]);
    }

    public function test_update_budget_fails_validation_without_type(): void
    {
        $response = $this->postJson(route('hub.models.budget.update'), [
            'limit' => 500,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_update_budget_fails_validation_with_invalid_type(): void
    {
        $response = $this->postJson(route('hub.models.budget.update'), [
            'type' => 'yearly',
            'limit' => 500,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_update_budget_fails_validation_without_limit(): void
    {
        $response = $this->postJson(route('hub.models.budget.update'), [
            'type' => 'monthly',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['limit']);
    }

    public function test_update_budget_fails_validation_with_negative_limit(): void
    {
        $response = $this->postJson(route('hub.models.budget.update'), [
            'type' => 'monthly',
            'limit' => -50,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['limit']);
    }

    public function test_it_gets_telemetry_successfully(): void
    {
        $telemetryData = [
            'success_rate' => 99.5,
            'avg_latency' => 350.0,
            'total_requests_24h' => 120,
            'total_cost_month' => 45.5,
            'active_providers_count' => 3,
            'cache_hit_rate' => 35,
            'cost_today' => 12.5,
            'tpm' => 200,
            'active_requests' => 2,
            'token_timeline' => [
                'labels' => ['Mon', 'Tue'],
                'input' => [10.5, 12.0],
                'output' => [5.5, 6.0],
            ],
        ];

        $this->mock(AiHubService::class, function (MockInterface $mock) use ($telemetryData) {
            $mock->shouldReceive('getTelemetry')
                ->once()
                ->andReturn($telemetryData);
        });

        $response = $this->getJson(route('hub.models.telemetry'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => $telemetryData,
            ]);
    }
}
