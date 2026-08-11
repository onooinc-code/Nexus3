<?php

namespace Tests\Feature\CredentialsHub;

use App\Models\CredentialsHub\Credential;
use App\Models\CredentialsHub\CredentialChat;
use App\Models\CredentialsHub\CredentialLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialsHubTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_credentials_hub_page_loads_with_chat_and_logs(): void
    {
        Credential::create([
            'title' => 'Test Server',
            'category' => 'panels',
            'subtitle' => 'Sub',
            'fields' => ['IP' => '1.1.1.1'],
        ]);

        CredentialChat::create(['role' => 'user', 'content' => 'Hello Agent']);
        CredentialLog::create(['action' => 'created', 'title' => 'Test Server']);

        $response = $this->actingAs($this->user)->get('/hub/credentials');

        $response->assertStatus(200);
        $response->assertSee('Nexus Credentials Hub');
        $response->assertSee('Test Server');
        $response->assertSee('Hello Agent');
    }

    public function test_single_line_arabic_credential_addition_via_agent(): void
    {
        $prompt = 'ضيف بيانات منصة مصر :   رقم الفون : 0144444444 كلمة السر : 2020200   العلامة : تيست';

        $response = $this->actingAs($this->user)->postJson('/hub/credentials/agent/chat', [
            'prompt' => $prompt,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('credentials', [
            'title' => 'منصة مصر',
        ]);

        $this->assertDatabaseHas('credential_chats', [
            'role' => 'user',
            'content' => $prompt,
        ]);
    }
}
