<?php

namespace Tests\Feature\Web;

use App\Services\AiHubService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class AiHubRoutingTest extends TestCase
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

        Schema::create('intent_routing', function (Blueprint $table) {
            $table->string('id');
            $table->string('intent_name')->unique();
            $table->string('default_provider_id')->nullable();
            $table->string('default_model_id')->nullable();
            $table->string('fallback_provider_id')->nullable();
            $table->string('fallback_model_id')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function test_it_stores_routing_rule_successfully(): void
    {
        $providerId = Str::uuid()->toString();
        $modelId = Str::uuid()->toString();

        DB::table('ai_providers')->insert([
            'id' => $providerId,
            'name' => 'OpenAI',
            'is_active' => true,
            'base_url' => 'https://api.openai.com',
        ]);

        DB::table('ai_models')->insert([
            'id' => $modelId,
            'provider_id' => $providerId,
            'name' => 'GPT-4',
            'api_identifier' => 'gpt-4',
            'is_active' => true,
        ]);

        $payload = [
            'intent_name' => 'test_intent',
            'default_provider_id' => $providerId,
            'default_model_id' => $modelId,
            'is_active' => true,
        ];

        $this->mock(AiHubService::class, function (MockInterface $mock) use ($payload) {
            $mock->shouldReceive('storeRoutingRule')
                ->once()
                ->with($payload)
                ->andReturn(array_merge($payload, ['id' => 'new-uuid']));
        });

        $response = $this->postJson(route('hub.models.routing.store'), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Routing rule saved successfully.',
                'data' => [
                    'intent_name' => 'test_intent',
                ],
            ]);
    }

    public function test_store_routing_rule_fails_validation_without_intent_name(): void
    {
        $response = $this->postJson(route('hub.models.routing.store'), [
            'is_active' => true,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['intent_name']);
    }

    public function test_store_routing_rule_fails_validation_with_invalid_provider(): void
    {
        $response = $this->postJson(route('hub.models.routing.store'), [
            'intent_name' => 'test_intent',
            'default_provider_id' => Str::uuid()->toString(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['default_provider_id']);
    }

    public function test_it_toggles_routing_rule_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('toggleRoutingRule')
                ->once()
                ->with('rule-uuid', true)
                ->andReturn(['id' => 'rule-uuid', 'is_active' => true]);
        });

        $response = $this->postJson(route('hub.models.routing.toggle', ['id' => 'rule-uuid']), [
            'is_active' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Routing rule toggled successfully.',
            ]);
    }

    public function test_toggle_routing_rule_fails_validation_without_is_active(): void
    {
        $response = $this->postJson(route('hub.models.routing.toggle', ['id' => 'rule-uuid']), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_active']);
    }

    public function test_it_deletes_routing_rule_successfully(): void
    {
        $this->mock(AiHubService::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteRoutingRule')
                ->once()
                ->with('rule-uuid');
        });

        $response = $this->deleteJson(route('hub.models.routing.delete', ['id' => 'rule-uuid']));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Routing rule deleted successfully.',
            ]);
    }
}
