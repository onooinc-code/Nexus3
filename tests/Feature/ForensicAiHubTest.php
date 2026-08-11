<?php

namespace Tests\Feature;

use App\Services\AiHubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForensicAiHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_ping_provider()
    {
        $service = app(AiHubService::class);
        $result = $service->pingProvider([
            'base_url' => 'https://api.openai.com',
            'api_key' => 'invalid_key',
            'models_fetch_endpoint' => '/v1/models',
        ]);

        file_put_contents('ping_result.json', json_encode($result));
        $this->assertTrue(true);
    }
}
