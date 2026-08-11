<?php

namespace Tests\Feature\PeopleConnect;

use App\Jobs\PeopleConnect\SyncWahaContactsJob;
use App\Models\Contact;
use App\Models\PeopleConnect\PeopleConnectConversation;
use App\Models\PeopleConnect\PeopleConnectMessage;
use App\Models\PeopleConnect\PeopleConnectSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for Sanctum auth
        $this->user = User::factory()->create();
    }

    public function test_stats_endpoint_returns_correct_data()
    {
        $contact = Contact::create(['name' => 'Test Contact', 'whatsapp_number' => '12345']);
        $conversation = PeopleConnectConversation::create([
            'contact_id' => $contact->id,
            'channel' => 'whatsapp',
            'provider' => 'waha',
            'provider_conversation_id' => '12345@c.us',
            'unread_count' => 1,
        ]);
        PeopleConnectSession::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/people-connect/stats');

        $response->assertStatus(200)
            ->assertJson([
                'total_contacts' => 1,
                'active_sessions' => 1,
                'unread_conversations' => 1,
                'status' => 'healthy',
            ]);
    }

    public function test_search_endpoint_finds_contact()
    {
        $contact = Contact::create(['name' => 'Jane Doe', 'phone' => '987654321', 'whatsapp_number' => '987654321']);
        PeopleConnectConversation::create([
            'contact_id' => $contact->id,
            'channel' => 'whatsapp',
            'provider' => 'waha',
            'provider_conversation_id' => '987654321@c.us',
            'unread_count' => 0,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/people-connect/search?q=Jane');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('Jane Doe', $response->json()[0]['name']);
    }

    public function test_livemsgs_endpoint_returns_messages()
    {
        $contact = Contact::create(['name' => 'Alice', 'whatsapp_number' => '111222']);
        $conversation = PeopleConnectConversation::create([
            'contact_id' => $contact->id,
            'channel' => 'whatsapp',
            'provider' => 'waha',
            'provider_conversation_id' => '111222@c.us',
        ]);
        PeopleConnectMessage::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'sender_type' => 'contact',
            'direction' => 'inbound',
            'body' => 'Hello World',
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/people-connect/livemsgs');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
        $this->assertEquals('Hello World', $response->json('data')[0]['body']);
    }

    public function test_update_reply_mode_validates_and_updates()
    {
        $contact = Contact::create(['name' => 'Bob', 'whatsapp_number' => '333444']);
        $conversation = PeopleConnectConversation::create([
            'contact_id' => $contact->id,
            'channel' => 'whatsapp',
            'provider' => 'waha',
            'provider_conversation_id' => '333444@c.us',
        ]);

        // Valid update
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/people-connect/conversations/{$conversation->id}/reply-mode", [
                'reply_mode' => 'ai_only',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('ai_only', $conversation->fresh()->reply_mode_effective);

        // Invalid update should fail validation
        $responseInvalid = $this->actingAs($this->user)
            ->postJson("/api/v1/people-connect/conversations/{$conversation->id}/reply-mode", [
                'reply_mode' => 'invalid_mode',
            ]);

        $responseInvalid->assertStatus(422);
    }

    public function test_trigger_sync_endpoint_dispatches_jobs()
    {
        Queue::fake();

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/people-connect/livemsgs/sync', ['type' => 'contacts']);

        $response->assertStatus(200);
        Queue::assertPushed(SyncWahaContactsJob::class);
    }
}
