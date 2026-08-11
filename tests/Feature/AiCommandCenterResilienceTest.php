<?php

namespace Tests\Feature;

use App\Models\AIApiKey;
use App\Models\AIProvider;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AiCommandCenterResilienceTest extends TestCase
{
    use RefreshDatabase;

    protected AIProvider $provider;

    protected EncryptedApiKeyStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = AIProvider::create([
            'id' => 'gemini_test',
            'name' => 'Gemini Test Provider',
            'base_url' => 'https://generativelanguage.googleapis.com',
            'driver_type' => 'gemini',
            'status' => 'active',
        ]);

        $this->storage = app(EncryptedApiKeyStorage::class);
    }

    public function test_api_key_rotation_skips_cooldown_keys(): void
    {
        $key1 = AIApiKey::create([
            'provider_id' => $this->provider->id,
            'key_hash' => Crypt::encryptString('secret_key_1'),
            'name' => 'Key 1',
            'is_active' => true,
            'cooldown_until' => now()->addMinutes(60),
            'error_count' => 1,
        ]);

        $key2 = AIApiKey::create([
            'provider_id' => $this->provider->id,
            'key_hash' => Crypt::encryptString('secret_key_2'),
            'name' => 'Key 2',
            'is_active' => true,
            'cooldown_until' => null,
            'error_count' => 0,
        ]);

        $usedKeyId = null;
        $result = $this->storage->getDecryptedKey($this->provider->id, $usedKeyId);

        $this->assertNotNull($result);
        $this->assertEquals($key2->id, $usedKeyId);
        $this->assertEquals('secret_key_2', $result);
    }

    public function test_flag_key_exhausted_sets_cooldown(): void
    {
        $key = AIApiKey::create([
            'provider_id' => $this->provider->id,
            'key_hash' => Crypt::encryptString('secret_key_to_exhaust'),
            'name' => 'Exhaustable Key',
            'is_active' => true,
        ]);

        $this->storage->flagKeyExhausted($key->id, 30);

        $key->refresh();
        $this->assertNotNull($key->cooldown_until);
        $this->assertEquals(1, $key->error_count);
        $this->assertTrue($key->cooldown_until->isFuture());
    }

    public function test_agent_settings_studio_page_loads(): void
    {
        $response = $this->get('/hub/people-connect/agent-settings');

        $response->assertStatus(200);
        $response->assertSee('AI Agent Settings');
        $response->assertSee('Multi-Key Rotation Engine');
    }
}
